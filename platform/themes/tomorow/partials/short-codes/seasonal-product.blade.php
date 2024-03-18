<div class="container-fluid">
    <section class="seasonal-product">
        <img src="{{ RvMedia::getImageUrl($imageBanner, 'image', false, RvMedia::getDefaultImage()) }}" alt="Not found">

        <div class="content">
            <h2>{{$title}}</h2>
            <p>{{$subtitle}}</p>
            <a href="{{route('public.products')}}" class="btn-seemore">Xem thêm</a>
        </div>
    </section>
</div>