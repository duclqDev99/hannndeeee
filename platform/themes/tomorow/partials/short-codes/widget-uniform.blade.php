<section class="widget-uniform">
    <div class="container-fluid">
        <div class="row no-gutters">
            <div class="col-lg-6 col-md-12 col-12">
                <div class="widgets widget_left">
                    <a href="{{$urlMan}}" class="widget_url"></a>
                    <div class="wrap_img box-shadow">
                        <img src="{{ RvMedia::getImageUrl($imgMan, 'medium', false, RvMedia::getDefaultImage()) }}" alt="" loading="eager">
                    </div>
                    <div class="content">
                        <h2>{{ $titleMan }}</h2>
                        <p>{{$subtitleMan}}</p>
                        {{-- <a href="{{$urlMan}}" class="btn-seemore">Mua ngay</a> --}}
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-12 col-12">
                <div class="widgets widget_right">
                    <a href="{{$urlGirl}}" class="widget_url"></a>
                    <div class="wrap_img box-shadow">
                        <img src="{{ RvMedia::getImageUrl($imgGirl, 'medium', false, RvMedia::getDefaultImage()) }}" alt="" loading="eager">
                    </div>
                    <div class="content">
                        <h2>{{ $titleGirl }}</h2>
                        <p>{{ $subtitleGirl }}</p>
                        {{-- <a href="{{$urlGirl}}" class="btn-seemore">Mua ngay</a> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>