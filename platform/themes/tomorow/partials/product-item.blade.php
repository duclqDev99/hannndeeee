@if ($product)
    @php 
        $arrSize = [];
    @endphp
    <div class="product" style="max-width: 604px;">
        <div class="product__wrapper box-shadow">
            <div class="product__thumbnail">
                @if ($product->isOutOfStock())
                    <div class="product__badges">
                        <span class="badge" style="background-color: #000">{{ __('Out Of Stock') }}</span>
                    </div>
                @else
                    @if ($product->productLabels->count() || $product->front_sale_price !== $product->price)
                        <div class="product__badges">
                            @foreach ($product->productLabels as $label)
                                <span class="badge" @if ($label->color) style="background-color: {{ $label->color }}" @endif>{{ $label->name }}</span>
                            @endforeach
                            @if ($product->front_sale_price !== $product->price)
                                <span class="badge badge--sale">{{ get_sale_percentage($product->price, $product->front_sale_price) }}</span>
                            @endif
                        </div>
                    @endif
                @endif
                {{-- <a class="product__overlay" href="{{ $product->url }}"></a> --}}

                <div class="widget-thumb">
                    <div class="img-default">
                        <img class="img__main" data-default="" alt="{{ $product->url }}" src="{{ RvMedia::getImageUrl($product->image) }}" alt="{{ $product->url }}" loading="lazy"/>
                    </div>
                    <div class="swiper product-main-slider" style="display: none;">
                        <div class="swiper-wrapper">
                            @foreach($product->images as $image)
                                @if(!empty($image))
                                    <div class="swiper-slide">
                                        <a href="{{ $product->url }}">
                                            <img class="img__variation" data-key="{{$product->id}}" src="{{ RvMedia::getImageUrl($image) }}" alt="{{ $product->nameHomepage }}" loading="lazy"/>
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>
                </div>
                <ul class="product__actions">
                    @if (EcommerceHelper::isCartEnabled() && !$product->isOutOfStock())
                        <li>
                            
                        </li>
                        <li><a class="add-to-cart-button" data-id="{{ $product->id }}" href="#" data-url="{{ route('public.cart.add-to-cart') }}"><i class="fa fa-cart-plus"></i></a></li>
                    @endif
                </ul>
                @if (count($product->variationAttributeSwatchesForProductList))
                    <ul class="product__variants color-swatch">
                        @foreach($product->variationAttributeSwatchesForProductList->unique('attribute_id') as $attribute)
                            @if ($attribute->display_layout == 'visual')
                                @php
                                    $attribute->setRelation('product', $product);
                                @endphp
                                <li>
                                    <div class="custom-checkbox" data-key="{{$attribute->product_id}}">
                                        <span style="background-color: {{ $attribute->color }}; cursor: initial;"></span>
                                    </div>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="product__content" data-mh="product-item">
                <a class="product__title" href="{{ $product->url }}">{!! BaseHelper::clean($product->nameHomepage) !!}</a>

                {!! apply_filters('ecommerce_before_product_price_in_listing', null, $product) !!}
                <p class="product__price @if ($product->front_sale_price !== $product->price) sale @endif">
                    <span>{{ format_price($product->front_sale_price_with_taxes) }}</span>
                    @if ($product->front_sale_price !== $product->price)
                        <del><span>{{ format_price($product->price_with_taxes) }}</span></del>
                    @endif
                </p>
                {!! apply_filters('ecommerce_after_product_price_in_listing', null, $product) !!}

                @if (EcommerceHelper::isReviewEnabled())
                    <div class="rating_wrap">
                        <div class="rating">
                            <div class="product_rate" style="width: {{ $product->reviews_avg * 20 }}%"></div>
                        </div>
                        <span class="rating_num">({{ $product->reviews_count }})</span>
                    </div>
                @endif

                @if (EcommerceHelper::isWishlistEnabled())
                    <a class="product__favorite js-add-to-wishlist-button" href="#" data-url="{{ route('public.wishlist.add', $product->id) }}">
                        <i class="fa fa-heart-o"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>
@endif
