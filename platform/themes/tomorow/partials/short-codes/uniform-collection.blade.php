<section class="uniform-collection">
    <img src="{{RvMedia::getImageUrl('ecommerce/blog-shape-1.png')}}" class="ns-blog-bg-shape-1 ns-blog-shape-bg" alt="blog-shape-1.png">
    <img src="{{RvMedia::getImageUrl('ecommerce/blog-shape-2.png')}}" class="ns-blog-bg-shape-2 ns-blog-shape-bg" alt="blog-shape-1.png">
    <img src="{{RvMedia::getImageUrl('ecommerce/blog-shape-3.png')}}" class="ns-blog-bg-shape-3 ns-blog-shape-bg" alt="blog-shape-1.png">
    <img src="{{RvMedia::getImageUrl('ecommerce/blog-shape-4.png')}}" class="ns-blog-bg-shape-4 ns-blog-shape-bg" alt="blog-shape-1.png">
    <div class="widget-main">
        <div class="widget-text item">
            <div class="content">
                <h2>{{$title}} <br> <span>{{$keytitle}}</span></h2>
                <p>{{$subtitle}}</p>
            </div>
        </div>
        <div class="widget-slider item">
            <!-- Swiper -->
            <div class="swiper swiperCollection">
                <div class="swiper-wrapper">
                    @if($products)
                    @foreach($products as $product)
                        <div class="swiper-slide">
                            <a href="/products/{{$product->slugable->key}}" class="banner-effect">
                                <img src="{{RvMedia::getImageUrl($product->image, 'image', false, RvMedia::getDefaultImage())}}" loading="lazy" alt="{{$product->nameHomepage}}">
                                <div class="content">
                                    <div class="name">{{$product->nameHomepage}}</div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                    @endif
                </div>
                {{-- <div class="swiper-scrollbar"></div> --}}
            </div>
        </div>
    </div>
</section>