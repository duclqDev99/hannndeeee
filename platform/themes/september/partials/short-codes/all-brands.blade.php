<section class="section--homepage" style="padding-top: 30px;">
    <div class="container">
        @if (clean($title))
            <div class="section__header">
                <h3>{!! BaseHelper::clean($title) !!}</h3>
                @if ($subtitle)
                    <p>{!! BaseHelper::clean($subtitle) !!}</p>
                @endif
            </div>
        @endif
        <div class="section__content">
            @if ($brands->count() > 0)
                <div class="row">
                    @foreach($brands as $brand)
                        <div class="col-md-3 col-6" style="margin-bottom: 10px;">
                            <a href="{{ $brand->url }}">
                                <img src="{{ RvMedia::getImageUrl($brand->logo) }}" alt="{{ $brand->name }}" loading="lazy"/>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
