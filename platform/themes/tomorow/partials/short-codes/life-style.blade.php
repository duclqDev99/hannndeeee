<div class="container-fluid mt-3">
    <section class="life-style box-shadow">
        <a href="{{$url}}" class="widget_url"></a>
        <img src="{{RvMedia::getImageUrl($image)}}" class="banner-default" alt="" loading="eager">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-7 col-lg-6 col-0"></div>
                <div class="col-xl-4 col-lg-6 col-12">
                    <div class="content">
                        <h3 class="title">{{$title}}</h3>
                        <div class="text-core">{{$subtitle}}</div>
                        <p>{{$note}}</p>
                        {{-- <a href="{{$url}}" class="btn-seemore">{{$textBtn}}</a> --}}
                    </div>
                </div>
                <div class="col-xl-1 col-lg-0 col-0">
                </div>
            </div>
        </div>
    </section>
</div>