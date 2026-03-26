<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
        <ul class="nav nav-tabs card-header-tabs" id="productFormTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-toggle="tab" data-target="#basic" type="button" role="tab">
                    <i class="fas fa-info-circle"></i> Basic Information
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-toggle="tab" data-target="#details" type="button" role="tab">
                    <i class="fas fa-align-left"></i> Descriptions
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-toggle="tab" data-target="#organization" type="button" role="tab">
                    <i class="fas fa-folder-tree"></i> Organization
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-toggle="tab" data-target="#seo" type="button" role="tab">
                    <i class="fas fa-search"></i> SEO & Meta
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-toggle="tab" data-target="#wholesale" type="button" role="tab">
                    <i class="fas fa-cube"></i> Wholesale
                </button>
            </li>
        </ul>
    </div>

    <form method="POST" id="product-form"
        action="{{ isset($product) ? route('product.update', $product->id) : route('product.insert') }}">
        @csrf
        @if (isset($product))
        @method('PATCH')
        @endif

        <input type="hidden" name="brand_id" value="{{ $vendorBrand?->id }}">

        <div class="tab-content" id="productFormTabsContent">
            <div class="tab-pane fade show active" id="basic" role="tabpanel">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="form-section">
                            <div class="form-section-title">Product Core Details</div>

                            <div class="form-group">
                                <label for="name" class="font-weight-bold">Product Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                    name="name" placeholder="Enter product name" value="{{ $name ?? '' }}"
                                    wire:model="name" required>
                                <small class="form-text text-muted">The public product title shown to customers.</small>
                                <div class="invalid-feedback client-validation-feedback" data-field="name"
                                    style="display:none;"></div>
                                @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="price" class="font-weight-bold">Price <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">NPR</span>
                                            </div>
                                            <input type="text" class="form-control @error('price') is-invalid @enderror"
                                                id="price" name="price" placeholder="0.00" value="{{ $price ?? '' }}"
                                                wire:model="price" required>
                                        </div>
                                        <div class="invalid-feedback client-validation-feedback" data-field="price"
                                            style="display:none;"></div>
                                        @error('price')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="warranty" class="font-weight-bold">Warranty</label>
                                        <input type="text" class="form-control @error('warranty') is-invalid @enderror"
                                            id="warranty" name="warranty" placeholder="e.g. 1 Year Warranty"
                                            value="{{ $warranty ?? '' }}" wire:model="warranty">
                                        @error('warranty')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            @if (isset($product))
                            <div class="form-group">
                                <label for="slug" class="font-weight-bold">URL Slug <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug"
                                    name="slug" placeholder="product-slug" value="{{ $slug ?? '' }}" wire:model="slug"
                                    required>
                                <small class="form-text text-muted">Used for product URLs on the shared
                                    storefront.</small>
                                @error('slug')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h6 class="font-weight-bold mb-3">
                                    <i class="fas fa-cog text-primary"></i> Status & Availability
                                </h6>

                                <div class="form-group">
                                    <label for="status" class="font-weight-bold">Status <span
                                            class="text-danger">*</span></label>
                                    <select id="status" name="status"
                                        class="form-control @error('status') is-invalid @enderror" required>
                                        <option value="">Select status</option>
                                        <option value="publish" @selected(($status ?? '' )==='publish' )>Publish
                                        </option>
                                        <option value="unpublish" @selected(($status ?? '' )==='unpublish' )>Unpublish
                                        </option>
                                    </select>
                                    <div class="invalid-feedback client-validation-feedback" data-field="status"
                                        style="display:none;"></div>
                                    @error('status')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="in_stock" class="font-weight-bold">Stock Status</label>
                                    <select id="in_stock" name="in_stock"
                                        class="form-control @error('in_stock') is-invalid @enderror">
                                        <option value="1" @selected(($in_stock ?? '1' )==='1' )>In Stock</option>
                                        <option value="0" @selected(($in_stock ?? '1' )==='0' )>Out of Stock</option>
                                    </select>
                                    @error('in_stock')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-0">
                                    <label for="alt_text" class="font-weight-bold">Alt Text <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('alt_text') is-invalid @enderror"
                                        id="alt_text" name="alt_text" placeholder="Describe the product image"
                                        value="{{ $alt_text ?? '' }}" wire:model="alt_text" required>
                                    <small class="form-text text-muted">Used for accessibility and SEO.</small>
                                    <div class="invalid-feedback client-validation-feedback" data-field="alt_text"
                                        style="display:none;"></div>
                                    @error('alt_text')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="details" role="tabpanel">
                <div class="form-section">
                    <div class="form-section-title">Product Descriptions</div>

                    <div class="form-group">
                        <label for="short_description" class="font-weight-bold">Short Description</label>
                        <textarea id="short_description" name="short_description"
                            class="form-control @error('short_description') is-invalid @enderror" rows="5"
                            placeholder="Short summary for listings and previews">{{ $short_description ?? '' }}</textarea>
                        <small id="short_description_stats" class="text-muted d-block mt-2"></small>
                        @error('short_description')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-0">
                        <label for="description" class="font-weight-bold">Full Description</label>
                        <textarea id="description" name="description"
                            class="form-control @error('description') is-invalid @enderror" rows="12"
                            placeholder="Detailed product description">{{ $description ?? '' }}</textarea>
                        <small id="description_stats" class="text-muted d-block mt-2"></small>
                        @error('description')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="organization" role="tabpanel">
                <div class="form-section">
                    <div class="form-section-title">Product Classification</div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body">
                                    <label class="font-weight-bold d-block">Assigned Brand</label>
                                    <div class="brand-lockup">
                                        <div class="brand-lockup__title">{{ $vendorBrand?->name ?? 'No brand assigned'
                                            }}</div>
                                        <div class="text-muted small">This vendor panel is locked to your assigned
                                            brand.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label for="categories" class="font-weight-bold">Categories <span
                                        class="text-danger">*</span></label>
                                <select name="category_id[]" id="categories"
                                    class="form-control @error('category_id') is-invalid @enderror" multiple>
                                    @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(in_array($category->id, $category_ids
                                        ?? []))>
                                        {{ $category->name }}
                                    </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Pick one or more categories for this
                                    product.</small>
                                <div id="categories_client_error" class="invalid-feedback d-block"
                                    style="display:none;">
                                    Please select at least one category.
                                </div>
                                @error('category_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="seo" role="tabpanel">
                <div class="form-section">
                    <div class="form-section-title">Search Engine Optimization</div>

                    <div class="form-group mb-0">
                        <label for="keywords" class="font-weight-bold">Keywords & Tags</label>
                        <div class="tags-container mb-3 p-2 border rounded bg-light" style="min-height:40px;">
                            @if (is_array($keywords))
                            @foreach ($keywords as $keyword)
                            <span class="badge badge-primary mr-1 mb-1">{{ $keyword }}</span>
                            @endforeach
                            @endif
                        </div>
                        <textarea class="form-control @error('keywords') is-invalid @enderror" id="keywords"
                            name="keywords" rows="3"
                            placeholder="Enter keywords separated by commas">{{ is_array($keywords) ? implode(',', $keywords) : ($keywords ?? '') }}</textarea>
                        <small class="form-text text-muted">Separate multiple keywords with commas.</small>
                        @error('keywords')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="wholesale" role="tabpanel">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="form-section">
                            <div class="form-section-title">Wholesale Settings</div>

                            <div class="form-group">
                                <label for="wholesale_product" class="font-weight-bold">Wholesale Availability</label>
                                <select id="wholesale_product" name="wholesale_product"
                                    class="form-control @error('wholesale_product') is-invalid @enderror">
                                    <option value="0" @selected(($wholesale_product ?? '0' )==='0' )>Retail Only
                                    </option>
                                    <option value="1" @selected(($wholesale_product ?? '0' )==='1' )>Enable Wholesale
                                    </option>
                                </select>
                                @error('wholesale_product')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-0">
                                <label for="wholesale_min_qty" class="font-weight-bold">Minimum Wholesale
                                    Quantity</label>
                                <input type="number" min="5"
                                    class="form-control @error('wholesale_min_qty') is-invalid @enderror"
                                    id="wholesale_min_qty" name="wholesale_min_qty" placeholder="e.g. 10"
                                    value="{{ $wholesale_min_qty ?? '' }}">
                                <small class="form-text text-muted">Minimum quantity required for wholesale pricing to
                                    apply.</small>
                                @error('wholesale_min_qty')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h6 class="font-weight-bold mb-3"><i class="fas fa-cube text-success"></i> Wholesale
                                    Notes</h6>
                                <ul class="small text-muted pl-3 mb-0">
                                    <li>Enable wholesale only when this product supports bulk orders.</li>
                                    <li>Choose a realistic minimum quantity for your inventory flow.</li>
                                    <li>These values save into the same shared product table as the main admin.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-light border-top d-flex justify-content-between align-items-center">
            <a href="{{ isset($product) ? route('product.show', $product->id) : route('products') }}"
                class="btn btn-outline-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> {{ isset($product) ? 'Save Changes' : 'Create Product' }}
            </button>
        </div>
    </form>


    <style>
        .tab-pane {
            padding: 1.5rem;
        }

        .form-section {
            margin-bottom: 1rem;
        }

        .form-section-title {
            font-size: 1.05rem;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #007bff;
            padding-bottom: 0.75rem;
            margin-bottom: 1.25rem;
        }

        .brand-lockup__title {
            font-size: 1.1rem;
            font-weight: 600;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('product-form');
        const tabsContainer = document.getElementById('productFormTabs');
        const categoriesSelect = document.getElementById('categories');
        const categoriesClientError = document.getElementById('categories_client_error');
        const requiredFields = ['name', 'price', 'status', 'alt_text'];

        function showTab(tabTrigger) {
            if (!tabTrigger) {
                return;
            }

            if (typeof $ !== 'undefined' && typeof $(tabTrigger).tab === 'function') {
                $(tabTrigger).tab('show');
            }

            const targetSelector = tabTrigger.getAttribute('data-target');

            tabsContainer.querySelectorAll('.nav-link').forEach(tab => {
                tab.classList.remove('active');
            });

            document.querySelectorAll('#productFormTabsContent .tab-pane').forEach(pane => {
                pane.classList.remove('show', 'active');
            });

            tabTrigger.classList.add('active');

            const targetPane = document.querySelector(targetSelector);
            if (targetPane) {
                targetPane.classList.add('show', 'active');
            }
        }

        function getTabTriggerForPane(pane) {
            return pane ? tabsContainer.querySelector('[data-target="#' + pane.id + '"]') : null;
        }

        function activateTabForElement(element) {
            const pane = element ? element.closest('.tab-pane') : null;
            const tabTrigger = getTabTriggerForPane(pane);

            if (tabTrigger) {
                showTab(tabTrigger);
            }
        }

        function setFieldClientValidity(fieldName, isValid, message) {
            const fieldElement = document.getElementById(fieldName);
            const feedbackElement = document.querySelector('.client-validation-feedback[data-field="' + fieldName + '"]');

            if (!fieldElement) {
                return;
            }

            fieldElement.classList.toggle('is-invalid', !isValid);

            if (feedbackElement) {
                feedbackElement.textContent = message || '';
                feedbackElement.style.display = isValid ? 'none' : 'block';
            }
        }

        function validateRequiredField(fieldName) {
            const fieldElement = document.getElementById(fieldName);

            if (!fieldElement) {
                return true;
            }

            const isValid = fieldElement.value.trim() !== '';
            setFieldClientValidity(fieldName, isValid, 'The ' + fieldName.replace('_', ' ') + ' field is required.');
            return isValid;
        }

        function hasSelectedCategories() {
            return categoriesSelect
                ? Array.from(categoriesSelect.options).some(option => option.selected)
                : true;
        }

        function setCategoriesClientValidity(isValid) {
            if (!categoriesSelect || !categoriesClientError) {
                return;
            }

            categoriesSelect.classList.toggle('is-invalid', !isValid);
            categoriesClientError.style.display = isValid ? 'none' : 'block';

            const select2Container = categoriesSelect.nextElementSibling;
            if (select2Container && select2Container.classList.contains('select2-container')) {
                const selection = select2Container.querySelector('.select2-selection');
                if (selection) {
                    selection.style.borderColor = isValid ? '' : '#dc3545';
                    selection.style.boxShadow = isValid ? '' : '0 0 0 0.2rem rgba(220, 53, 69, 0.15)';
                }
            }
        }

        function updateStats(textareaId, statsId) {
            const textarea = document.getElementById(textareaId);
            const stats = document.getElementById(statsId);

            if (!textarea || !stats) {
                return;
            }

            function refresh() {
                const text = textarea.value || '';
                const characters = text.length;
                const words = text.trim() ? text.trim().split(/\s+/).length : 0;
                stats.innerHTML =
                    '<span class="badge badge-info mr-1">' + characters + ' Characters</span>' +
                    '<span class="badge badge-secondary">' + words + ' Words</span>';
            }

            textarea.addEventListener('input', refresh);
            refresh();
        }

        function updateTags() {
            const keywordsInput = document.getElementById('keywords');
            const tagsContainer = document.querySelector('.tags-container');

            if (!keywordsInput || !tagsContainer) {
                return;
            }

            const keywords = keywordsInput.value.split(',').map(kw => kw.trim()).filter(Boolean);
            tagsContainer.innerHTML = '';

            keywords.forEach(function(keyword) {
                const badge = document.createElement('span');
                badge.className = 'badge badge-primary mr-1 mb-1';
                badge.textContent = keyword;
                tagsContainer.appendChild(badge);
            });
        }

        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('#categories').select2({
                width: '100%',
                placeholder: 'Select categories'
            });
            $('#status').select2({
                width: '100%'
            });
            $('#in_stock').select2({
                width: '100%'
            });
            $('#wholesale_product').select2({
                width: '100%'
            });
        }

        if (typeof ClassicEditor !== 'undefined') {
            ['short_description', 'description'].forEach(function(id) {
                const el = document.getElementById(id);
                if (el) {
                    ClassicEditor.create(el).catch(function(error) {
                        console.error(error);
                    });
                }
            });
        }

        requiredFields.forEach(function(fieldName) {
            const fieldElement = document.getElementById(fieldName);
            if (!fieldElement) {
                return;
            }

            const eventName = fieldElement.tagName === 'SELECT' ? 'change' : 'input';
            fieldElement.addEventListener(eventName, function() {
                validateRequiredField(fieldName);
            });
        });

        if (categoriesSelect) {
            categoriesSelect.addEventListener('change', function() {
                setCategoriesClientValidity(hasSelectedCategories());
            });
        }

        const keywordsInput = document.getElementById('keywords');
        if (keywordsInput) {
            keywordsInput.addEventListener('input', updateTags);
            keywordsInput.addEventListener('blur', updateTags);
            updateTags();
        }

        updateStats('short_description', 'short_description_stats');
        updateStats('description', 'description_stats');

        if (form) {
            form.addEventListener('submit', function(event) {
                for (const fieldName of requiredFields) {
                    if (!validateRequiredField(fieldName)) {
                        event.preventDefault();
                        activateTabForElement(document.getElementById(fieldName));
                        return;
                    }
                }

                if (!hasSelectedCategories()) {
                    event.preventDefault();
                    setCategoriesClientValidity(false);
                    activateTabForElement(categoriesSelect);
                }
            });
        }
    });
    </script>
</div>