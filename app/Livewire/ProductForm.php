<?php

namespace App\Livewire;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProductForm extends Component
{
    public $categories;
    public $vendorBrand;
    public $message;
    public $name;
    public $price;
    public $warranty;
    public $product;
    public $description;
    public $short_description;
    public $alt_text;
    public $keywords;
    public $slug;
    public $in_stock;
    public $status;
    public $wholesale_product = false;
    public $wholesale_min_qty;
    public $category_ids = [];

    public function mount()
    {
        $this->vendorBrand = Auth::user()?->brand;
        $this->categories = Category::with('children')
            ->doesntHave('children')
            ->get();

        $this->name = old('name', $this->product->name ?? null);
        $this->price = old('price', $this->product->price ?? null);
        $this->warranty = old('warranty', $this->product->warranty ?? null);
        $this->description = old('description', $this->product->description ?? null);
        $this->short_description = old('short_description', $this->product->short_description ?? null);
        $this->alt_text = old('alt_text', $this->product->alt_text ?? null);
        $this->slug = old('slug', $this->product->slug ?? null);
        $this->status = old('status', $this->product->status ?? null);
        $this->in_stock = $this->normalizeBooleanString(old('in_stock', $this->product->in_stock ?? 1), '1');
        $this->wholesale_product = $this->normalizeBooleanString(
            old('wholesale_product', $this->product->wholesale_product ?? false),
            '0'
        );
        $this->wholesale_min_qty = old('wholesale_min_qty', $this->product->wholesale_min_qty ?? null);
        $this->category_ids = array_map(
            'intval',
            old('category_id', $this->product?->categories->pluck('id')->all() ?? [])
        );

        $keywords = old('keywords', $this->product->keywords ?? '');
        $this->keywords = is_array($keywords)
            ? $keywords
            : array_values(array_filter(array_map('trim', explode(',', (string) $keywords))));
    }

    private function normalizeBooleanString($value, string $default): string
    {
        if ($value === null || $value === '') {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ? '1' : '0';
    }

    public function render()
    {
        return view('admin.livewire.product-form');
    }
}
