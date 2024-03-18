@php
    [$categories, $brands, $tags, $rand, $categoriesRequest, $urlCurrent, $categoryId, $maxFilterPrice] = 
EcommerceHelper::dataForFilter($category ?? null);

    $categories = ProductCategoryHelper::getActiveTreeCategories();

    Theme::asset()->usePath()
                ->add('custom-scrollbar-css', 'plugins/mcustom-scrollbar/jquery.mCustomScrollbar.css');
    Theme::asset()->container('footer')->usePath()
                ->add('custom-scrollbar-js', 'plugins/mcustom-scrollbar/jquery.mCustomScrollbar.js', ['jquery']);
@endphp

<aside class="widget widget--shop">
    <h4 class="widget__title">{{ __('Product Categories') }}</h4>
    <div class="widget__content">
        <ul class="ps-list--categories">
            @foreach($categories as $category)
                <li class="@if ($urlCurrent == $category->url || (!empty($categoriesRequest && in_array($category->id, $categoriesRequest)))) current-menu-item @endif @if ($category->activeChildren->count()) menu-item-has-children @endif">
                    <a href="{{ $category->url }}">{!! BaseHelper::clean($category->name) !!}</a>
                    @if ($category->activeChildren->count())
                        @include(Theme::getThemeNamespace() . '::views.ecommerce.includes.sub-categories', ['children' => $category->activeChildren])
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
</aside>
@if ($brands->isNotEmpty())
    <aside class="widget widget--shop">
        <h4 class="widget__title">{{ __('Brands') }}</h4>
        <div class="widget__content">
            <ul class="widget__links list--plus ps-custom-scrollbar">
                @foreach($brands as $brand)
                    <li @if (URL::current() == $brand->url) class="active" @endif><a href="{{ $brand->url }}">{!! BaseHelper::clean($brand->name) !!} <span class="d-inline-block">({{ $brand->products_count }})</span></a></li>
                @endforeach
            </ul>
        </div>
    </aside>
@endif
@if ($tags->isNotEmpty())
    <aside class="widget widget--shop widget--tags">
        <h4 class="widget__title">{{ __('Product Tags') }}</h4>
        <div class="widget__content">
            <ul class="list--tags ps-custom-scrollbar">
                @foreach($tags as $tag)
                    <li @if (URL::current() == $tag->url) class="active" @endif><a href="{{ $tag->url }}">{{ $tag->name }} ({{ $tag->products_count }})</a></li>
                @endforeach
            </ul>
        </div>
    </aside>
@endif
<aside class="widget widget--shop">
    @if ($maxFilterPrice)
        <h4 class="widget__title">{{ __('By Price') }}</h4>
        <div class="widget__content nonlinear-wrapper">
            <div class="nonlinear" data-min="0" data-max="{{ $maxFilterPrice }}"></div>
            <div class="ps-slider__meta">
                <input class="product-filter-item product-filter-item-price-0" name="min_price" value="{{ BaseHelper::stringify(request()->input('min_price', 0)) }}" type="hidden">
                <input class="product-filter-item product-filter-item-price-1" name="max_price" value="{{ BaseHelper::stringify(request()->input('max_price', $maxFilterPrice)) }}" type="hidden">
                <span class="ps-slider__value">
                    <span class="ps-slider__min"></span> {{ get_application_currency()->title }}</span> - <span class="ps-slider__value"><span class="ps-slider__max"></span> {{ get_application_currency()->title }}
                </span>
            </div>
        </div>
    @endif

    {!! render_product_swatches_filter([
        'view' => Theme::getThemeNamespace() . '::views.ecommerce.attributes.attributes-filter-renderer'
    ]) !!}
</aside>
