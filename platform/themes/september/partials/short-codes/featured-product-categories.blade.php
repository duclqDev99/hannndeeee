<section class="section--homepage">
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
        <div class="section__content row align-items-center">
            <div class="col-12">
                <div class="cat_slider carousel-slider cat_style1 mt-4 mt-md-0 carousel_slider owl-carousel owl-theme nav_style5" data-loop="false" data-dots="false" data-nav="true" data-margin="30" data-responsive='{"0":{"items": "2"}, "480":{"items": "3"}, "576":{"items": "4"}, "768":{"items": "5"}, "991":{"items": "6"}, "1199":{"items": "7"}}'>
                    @foreach($categories as $category)
                        <div class="item">
                            <div class="categories_box">
                                <a href="{{ $category->url }}">
                                    <img src="{{ RvMedia::getImageUrl($category->image, null, false, RvMedia::getDefaultImage()) }}" alt="{{ $category->name }}"/>
                                    <span class="d-block text-center">{{ $category->name }}</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
