<section class="section--blog">
    <div class="section__header">
        <h1>{{ $gallery->name }}</h1>
    </div>
    <div class="section__content">
        <p>
            {{ $gallery->description }}
        </p>
        <div id="list-photo">
            @foreach (gallery_meta_data($gallery) as $image)
                @if ($image)
                    <div class="item" data-src="{{ RvMedia::getImageUrl(Arr::get($image, 'img')) }}" data-sub-html="{{ BaseHelper::clean(Arr::get($image, 'description')) }}">
                        <div class="photo-item">
                            <div class="thumb">
                                <a href="{{ RvMedia::getImageUrl(Arr::get($image, 'img')) }}">
                                    <img src="{{ RvMedia::getImageUrl(Arr::get($image, 'img')) }}" alt="{{ BaseHelper::clean(Arr::get($image, 'description')) }}" loading="lazy"/>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
        <br />
        {!! apply_filters(BASE_FILTER_PUBLIC_COMMENT_AREA, null, $gallery) !!}
    </div>
</section>
