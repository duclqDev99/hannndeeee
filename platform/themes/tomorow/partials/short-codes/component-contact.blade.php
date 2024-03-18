<section class="component-contact">
    <div class="container">
        <div class="ns-cta-inner-4">
            <img src="{{ RvMedia::getImageUrl($imageBanner, 'image', false, RvMedia::getDefaultImage()) }}" alt="" class="ns-cta-bg-4">
            <div class="ns-cta-wrap-4">
                <div class="ns-cta-left-4">
                    <div class="ns-cta-img-4">
                        <img src="{{ RvMedia::getImageUrl($imageFeature, 'image', false, RvMedia::getDefaultImage()) }}" alt="">
                    </div>
                    <div class="ns-cta-content-4 d-none">
                        <h3 class="ns-cta-content-title-4">{{$title}}</h3>
                    </div>
                </div>
                <div class="ns-cta-btn-4">
                    <div class="ns-cta-content-4">
                        <h3 class="ns-cta-content-title-4">{{$title}}</h3>
                    </div>
                    <a href="/contact" class="ns-theme-btn">{{__('Liên hệ với chúng tôi')}} <i class="fa fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>