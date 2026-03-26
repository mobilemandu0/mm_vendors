<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Feature;
use App\Models\Product;
use App\Models\ProductSpecification;
use App\Models\ProductVariant;
use App\Models\Specification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $userBrandId = $this->currentUserBrandId();
        $selected_brand = $request->brand_id ?? $userBrandId;
        $selected_categories = $request->category_id ?? [];
        $search_query = $request->input('query') ?? null;
        $itemsPerPage = $request->input('items_per_page', 20);

        $products = Product::with(['categories', 'brand'])
            ->where('brand_id', $userBrandId)
            ->when($selected_brand, fn($query) => $query->where('brand_id', $selected_brand))
            ->when(!empty($selected_categories), fn($query) => $query->whereHas('categories', function ($q) use ($selected_categories) {
                $q->whereIn('categories.id', $this->getAllCategoryIds($selected_categories));
            }))
            ->when($search_query, fn($query) => $query->where(function ($q) use ($search_query) {
                foreach (array_map('trim', explode(',', $search_query)) as $term) {
                    $q->orWhere('name', 'like', "%$term%")
                        ->orWhere('description', 'like', "%$term%");
                }
            }))
            ->orderByDesc('id')
            ->paginate($itemsPerPage);

        return view('admin.product.index', [
            'products' => $products,
            'brands' => Brand::where('id', $userBrandId)->get(),
            'categories' => Category::all(),
            'selected_brand' => $selected_brand,
            'selected_categories' => $selected_categories,
            'query' => $search_query,
        ]);
    }

    /**
     * Get all category IDs including selected and their descendants.
     */
    protected function getAllCategoryIds(array $selected_categories)
    {
        $allCategoryIds = [];

        $categories = Category::whereIn('id', $selected_categories)->with('children')->get();

        foreach ($categories as $category) {
            $allCategoryIds[] = $category->id;
            $allCategoryIds = array_merge($allCategoryIds, $category->getAllChildrenIds()->toArray());
        }

        return $allCategoryIds;
    }


    public function create()
    {
        $brands = Brand::where('id', $this->currentUserBrandId())->get();
        return view('admin.product.form', compact('brands'));
    }

    public function insert(ProductRequest $request)
    {
        $data = $request->validated();
        $data['brand_id'] = $this->currentUserBrandId();
        unset($data['categories']);
        $product = Product::create($data);
        $product->categories()->sync($request->category_id);
        toastr()->success('Product Created Successfully!');
        return redirect()->route('product.specification.create', $product->id);
    }

    public function show(Product $product)
    {
        $this->assertUserOwnsProduct($product);
        return view('admin.product.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $this->assertUserOwnsProduct($product);
        return view('admin.product.form', compact('product'));
    }

    public function update(Product $product, ProductRequest $request)
    {
        $this->assertUserOwnsProduct($product);
        $data = $request->validated();
        $data['brand_id'] = $this->currentUserBrandId();
        unset($data['categories']);
        $product->update($data);
        $product->categories()->sync($request->category_id);
        toastr()->success('Product Edited Successfully!');
        return redirect()->route('products');
    }

    public function delete(Product $product)
    {
        $this->assertUserOwnsProduct($product);
        $product->delete();
        toastr()->success('Product Deleted Successfully!');
        return redirect()->route('products');
    }

    public function createSpecifications(Product $product)
    {
        $this->assertUserOwnsProduct($product);
        $specifications = $product->categories()
            ->first()
            ->specifications()
            ->withPivot('display_order')
            ->orderBy('category_specification.display_order')
            ->get();

        $product_specifications = [];
        foreach ($product->specifications()->get() as $p_spec) {
            $product_specifications[$p_spec->pivot->specification_id] = $p_spec->pivot->value;
        }

        return view('admin.product.specifications-form', compact('product', 'specifications', 'product_specifications'));
    }

    public function insertSpecifications(Product $product, Request $request)
    {
        $this->assertUserOwnsProduct($product);
        $validated = $request->validate([
            'value' => 'required|array',
        ]);
        foreach ($request->value as $specification_id => $value) {
            if ($value != null && $value != '') {
                $product_specification = ProductSpecification::where('product_id', $product->id)
                    ->where('specification_id', $specification_id)
                    ->first();
                if (!$product_specification)
                    $product_specification = new ProductSpecification();
                $product_specification->product_id = $product->id;
                $product_specification->specification_id = $specification_id;
                $product_specification->value = $value;
                $product_specification->save();
            }
        }
        toastr()->success('Product Created Successfully!');
        return redirect()->route('product.feature.create', $product->id);
    }

    public function manageSpecifications(Product $product)
    {
        $this->assertUserOwnsProduct($product);
        $specifications = $product->categories()
            ->first()
            ->specifications()
            ->withPivot('display_order')
            ->orderBy('category_specification.display_order')
            ->get();
        $product_specifications = [];
        foreach ($specifications as $specification) {
            $specification_data = $product->specifications()->where('specification_id', $specification->id)->first();
            if ($specification_data !== null) {
                $product_specifications[] = $specification_data;
            }
        }
        return view('admin.product.specifications', compact('product_specifications', 'product'));
    }

    public function editSpecifications(ProductSpecification $product_specification)
    {
        $this->assertUserOwnsProductSpecification($product_specification);
        return view('admin.product.specifications-form', compact('product_specification'));
    }

    public function updateSpecifications(ProductSpecification $product_specification, Request $request)
    {
        $this->assertUserOwnsProductSpecification($product_specification);
        if (isset($request->name) && $request->name != '') {
            $specification = Specification::firstOrCreate([
                'name' =>  $request->name
            ]);
            $product_specification->specification_id = $specification->id;
        }
        $product_specification->value = $request->value;
        $product_specification->save();
        toastr()->success('Product Edited Successfully!');
        return redirect()->route('product.specifications', $product_specification->product->id);
    }

    public function deleteSpecifications(Product $product, Specification $specification)
    {
        $this->assertUserOwnsProduct($product);
        $product->specifications()->detach($specification->id);
        toastr()->success('Product Specification Deleted Successfully!');
        return redirect()->route('product.specifications', $product->id);
    }
    public function deleteAllSpecifications(Product $product)
    {
        $this->assertUserOwnsProduct($product);
        $product->specifications()->detach();
        toastr()->success('Product Specification Deleted Successfully!');
        return redirect()->route('product.specifications', $product->id);
    }

    public function createFeatures(Product $product)
    {
        $this->assertUserOwnsProduct($product);
        return view('admin.product.features-form', compact('product'));
    }

    public function insertFeatures(Product $product, Request $request)
    {
        $this->assertUserOwnsProduct($product);
        $feature = new Feature();
        $feature->feature = $request->feature;
        $feature->product_id = $product->id;
        $feature->save();
        toastr()->success('Product Feature Created Successfully!');
        return redirect()->route('product.images', $product->id);
    }

    public function manageFeatures(Product $product)
    {
        $this->assertUserOwnsProduct($product);
        $product_features = Feature::where('product_id', $product->id)->with('product')->get();
        return view('admin.product.features', compact('product_features', 'product'));
    }

    public function editFeatures(Feature $feature)
    {
        $this->assertUserOwnsFeature($feature);
        return view('admin.product.features-form', compact('feature'));
    }

    public function updateFeatures(Feature $feature, Request $request)
    {
        $this->assertUserOwnsFeature($feature);
        $feature->feature = $request->feature;
        $feature->save();
        toastr()->success('Product Feature Edited Successfully!');
        return redirect()->route('product.features', $feature->product->id);
    }

    public function deleteFeatures(Feature $feature)
    {
        $this->assertUserOwnsFeature($feature);
        $feature->delete();
        toastr()->success('Product Feature Deleted Successfully!');
        return redirect()->route('product.features', $feature->product_id);
    }

    public function deleteAllFeatures(Product $product)
    {
        $this->assertUserOwnsProduct($product);
        $product->features()->delete();
        toastr()->success('Product Features Deleted Successfully!');
        return redirect()->route('product.features', $product->id);
    }

    public function manageImages(Product $product)
    {
        $this->assertUserOwnsProduct($product);
        return view('admin.product.images', compact('product'));
    }

    public function insertImages(Product $product, Request $request)
    {
        $this->assertUserOwnsProduct($product);
        $product->addMedia($request->file('image'))->toMediaCollection();
    }


    public function updateImages(Product $product, Request $request)
    {
        $this->assertUserOwnsProduct($product);
        $media = $product->getMedia()->where('file_name', $request->name)->first();
        $media->order_column = $request->count;
        $media->save();
    }

    public function deleteImages(Product $product, Request $request)
    {
        $this->assertUserOwnsProduct($product);
        $product->getMedia()->where('uuid', $request->id)->first()->delete();
    }

    public function createVariants(Product $product)
    {
        $this->assertUserOwnsProduct($product);
        $product = Product::with(['categories.specifications' => function ($query) {
            $query->wherePivot('is_variant', true);
        }])->find($product->id);
        $variant_specifications = $product->categories->first()->specifications;
        return view('admin.product.variants-form', compact('product', 'variant_specifications'));
    }



    public function insertVariants(Request $request, Product $product)
    {
        $this->assertUserOwnsProduct($product);
        $validated = $request->validate([
            'variants' => 'required|array',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.stock' => 'required|integer|min:0',
        ]);
        $variants = $request['variants'];
        foreach ($variants as $variant_data) {
            $sku = $this->generateUniqueSku($variant_data, $product);
            $variant = $product->variants()->create([
                'price' => $variant_data['price'],
                'stock_quantity' => $variant_data['stock'],
                'sku' => $sku,
            ]);

            foreach ($variant_data as $key => $value) {
                if ($key !== 'price' && $key !== 'stock_quantity' && $key !== 'sku') {
                    $specification = Specification::where('name', $key)->first();

                    if ($specification) {
                        $variant->variant_options()->create([
                            'specification_id' => $specification->id,
                            'value' => $value,
                        ]);
                    }
                }
            }
        }
        toastr()->success('Variants created successfully.');
        return redirect()->back()->with('success', 'Variants created successfully.');
    }


    public function manageVariants(Product $product)
    {
        $this->assertUserOwnsProduct($product);
        $product->load(['variants.variant_options.specification']);

        $variants = $product->variants->map(function ($variant) {
            return [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'price' => $variant->price,
                'stock' => $variant->stock_quantity,
                'options' => $variant->variant_options->map(fn($option) => [
                    'specification' => $option->specification->name,
                    'value' => $option->value,
                ])->values(),
            ];
        })->values();

        return view('admin.product.variants', compact('product', 'variants'));
    }


    private function generateUniqueSku($variant_data, $product)
    {
        $sku_parts = [];
        $sku_parts[0] = strtoupper($product->slug);

        foreach ($variant_data as $key => $value) {
            if ($key !== 'price' && $key !== 'stock' && $key !== 'sku') {
                $sku_parts[] = strtoupper($key) . '-' . strtoupper($value);
            }
        }

        if (empty($sku_parts)) {
            throw new \Exception("Failed to generate SKU. Missing required specifications.");
        }

        return implode('-', $sku_parts);
    }



    public function editVariants(Product $product, ProductVariant $variant)
    {
        $this->assertUserOwnsProduct($product);
        $this->assertVariantBelongsToProduct($variant, $product);

        return view('admin.product.variants_edit', compact('product', 'variant'));
    }

    public function updateVariants(Request $request, Product $product, ProductVariant $variant)
    {
        $this->assertUserOwnsProduct($product);
        $this->assertVariantBelongsToProduct($variant, $product);
        $request->validate([
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
        ]);

        $variant->update([
            'price' => $request->price,
            'stock_quantity' => $request->stock_quantity,
        ]);
        toastr()->success('Variants edited successfully.');
        return redirect()->route('product.variants', $product->id)
            ->with('success', 'Variant updated successfully.');
    }


    public function deleteVariants(Product $product, ProductVariant $variant)
    {
        $this->assertUserOwnsProduct($product);
        $this->assertVariantBelongsToProduct($variant, $product);

        $variant->delete();

        return redirect()->route('product.variants', $product->id)
            ->with('success', 'Variant deleted successfully.');
    }
    public function deleteAllVariants(Product $product)
    {
        $this->assertUserOwnsProduct($product);
        $product->variants()->delete();
        toastr()->success('Product Variants Deleted Successfully!');
        return redirect()->route('product.variants', $product->id);
    }
    public function export(Request $request)
    {
        $userBrandId = $this->currentUserBrandId();
        $selected_brand = $request->brand_id ?? $userBrandId;
        $selected_categories = $request->category_id ?? [];
        $query = $request->get('query') ?? null;

        $products = Product::with(['categories', 'brand', 'variants', 'media'])
            ->where('brand_id', $userBrandId)
            ->when($selected_brand, fn($queryBuilder) => $queryBuilder->where('brand_id', $selected_brand))
            ->when(!empty($selected_categories), fn($queryBuilder) => $queryBuilder->whereHas('categories', function ($q) use ($selected_categories) {
                $q->whereIn('categories.id', $this->getAllCategoryIds($selected_categories));
            }))
            ->when($query, fn($queryBuilder, $query) => $queryBuilder->where(function ($q) use ($query) {
                foreach (array_map('trim', explode(',', $query)) as $term) {
                    $q->orWhere('name', 'like', "%$term%")
                        ->orWhere('description', 'like', "%$term%");
                }
            }))
            ->orderByDesc('id')
            ->get();

        $csvData = [];
        $csvData[] = ['ID', 'Name', 'Description', 'Price', 'Category', 'Brand', 'Variant', 'Status', 'Image Link', 'Product Link'];

        foreach ($products as $product) {
            $categories = $product->categories->pluck('name')->implode(', ');
            $brand = $product->brand ? $product->brand->name : 'N/A';
            $variants = $product->variants->pluck('name')->implode(', ');
            $imageLink = $product->getFirstMedia() ? $product->getFirstMedia()->getUrl() : 'N/A';
            $productLink = "https://www.mobilemandu.com/products/" . $product->slug;

            $csvData[] = [
                $product->id,
                $product->name,
                $product->description,
                $product->price,
                $categories,
                $brand,
                $variants,
                ucfirst($product->status) . 'ed',
                $imageLink,
                $productLink,
            ];
        }

        $filename = 'products_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $handle = fopen('php://temp', 'w+');

        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);

        return Response::stream(function () use ($handle) {
            fpassthru($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    private function currentUserBrandId(): int
    {
        $brandId = Auth::user()->brand_id;

        if (!$brandId) {
            abort(403, 'No brand is assigned to this vendor.');
        }

        return (int) $brandId;
    }

    private function assertUserOwnsProduct(Product $product): void
    {
        if ((int) $product->brand_id !== $this->currentUserBrandId()) {
            abort(403, 'Unauthorized action.');
        }
    }

    private function assertUserOwnsProductSpecification(ProductSpecification $productSpecification): void
    {
        $productSpecification->loadMissing('product');
        $this->assertUserOwnsProduct($productSpecification->product);
    }

    private function assertUserOwnsFeature(Feature $feature): void
    {
        $feature->loadMissing('product');
        $this->assertUserOwnsProduct($feature->product);
    }

    private function assertVariantBelongsToProduct(ProductVariant $variant, Product $product): void
    {
        if ((int) $variant->product_id !== (int) $product->id) {
            abort(404);
        }
    }
}
