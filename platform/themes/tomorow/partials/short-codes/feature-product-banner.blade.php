<div class="container-fluid">
    <section class="section--homepage feature-product-banner" style="--bg-main: url({{ RvMedia::getImageUrl($imageBanner, 'image', false, RvMedia::getDefaultImage()) }})">
        <div class="widget-main container-fluid">
            <div class="widget-item">
                <div class="widget-left">
                    <a href="{{$url}}">
                        <picture class="banner-effect">
                            <img src="{{ RvMedia::getImageUrl($imageFeature, 'image', false, RvMedia::getDefaultImage()) }}" loading="lazy"/>
                        </picture>
                    </a>
                    <div class="content">
                        <a href="{{route('public.products')}}"><h4>{{$titleFeature}}</h4></a>
                    </div>
                </div>
            </div>
            <div class="widget-item">
                <div class="widget-right">
                    <div class="wg-header">
                        <h3 class="text-center text-white">{!! BaseHelper::clean($title) !!}</h3>
                    </div>
                    {{-- <div class="swiper productFeature">
                        <div class="swiper-wrapper">
                            @foreach($products as $product)
                                <div class="swiper-slide">
                                    <a href="/{{$product->slugable->prefix}}/{{$product->slugable->key}}">
                                        <div class="widget-product">
                                            <img src="{{RvMedia::getImageUrl($product->image, 'medium', false, RvMedia::getDefaultImage())}}" class="img-fluid" alt="">
    
                                            <div class="wd-content">
                                                <p class="name">{{$product->name}}</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-pagination"></div>
                    </div> --}}
    
                    <div class="ns-project-wrap">
                        <div class="project-active swiper-container overflow-hidden">
                            <div class="swiper-wrapper">
                                @foreach($products as $product)
                                    @php 
                                        // Lấy ba phần tử đầu tiên của mảng
                                        $firstThreeItems = array_slice($product->categories->toArray(), 0, 3);
                                        // Biến để lưu chuỗi kết quả
                                        $resultString = '';
    
                                        // Duyệt qua mảng và xây dựng chuỗi
                                        foreach ($firstThreeItems as $item) {
                                            // Nối tên của mỗi item và dấu '/'
                                            $resultString .= $item['name'] . ' / ';
                                        }
                                        // Loại bỏ dấu '/' ở cuối chuỗi
                                        $resultString = rtrim($resultString, ' / ');
                                    @endphp
                                    <div class="swiper-slide">
                                        <div class="ns-project-item">
                                            <div class="ns-project-img w_img">
                                                <a href="/{{$product->slugable->prefix}}/{{$product->slugable->key}}">
                                                    <img src="{{RvMedia::getImageUrl($product->image, 'medium', false, RvMedia::getDefaultImage())}}" alt="Not Found">
                                                </a>
                                            </div>
                                            <div class="ns-project-content">
                                                <div class="ns-project-content-info">
                                                    <h4 class="ns-project-content-title"><a href="/{{$product->slugable->prefix}}/{{$product->slugable->key}}">{{$product->nameHomepage}}</a></h4>
                                                    <span>{{$resultString}}</span>
                                                </div>
                                                <div class="ns-project-content-btn">
                                                    <a href="/{{$product->slugable->prefix}}/{{$product->slugable->key}}"><i class="fa fa-chevron-circle-right"></i></a>
                                                </div>
                                            </div>
                                            <span class="ns-project-shape-1 ns-project-shape"></span>
                                            <span class="ns-project-shape-2 ns-project-shape"></span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="ns-project-pagination mt-50"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
