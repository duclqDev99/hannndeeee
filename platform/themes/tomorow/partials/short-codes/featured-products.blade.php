<section class="section--homepage home-products">
    <img src="{{ RvMedia::getImageUrl('ecommerce/testimonial-shape.png', 'image', false, RvMedia::getDefaultImage()) }}" class="ns-cta-map-1" alt="testimonial-shape.png">
    <img src="{{ RvMedia::getImageUrl('ecommerce/testimonial-shape.png', 'image', false, RvMedia::getDefaultImage()) }}" class="ns-cta-map-2" alt="testimonial-shape.png">
    <div class="container">
        <div class="section__header">
            <h3>{!! BaseHelper::clean($title) !!}</h3>
            @if ($description)
                <p>{!! BaseHelper::clean($description) !!}</p>
            @endif
            @if ($subtitle)
                <p>{!! BaseHelper::clean($subtitle) !!}</p>
            @endif
        </div>
    </div>
    <div class="container-fluid">
        <div class="section__content">
            <div class="row">
                @foreach($products as $product)
                    <div class="widget-item col-xl-4 col-lg-6 col-md-6 col-6">
                        {!! Theme::partial('product-item', compact('product')) !!}
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
