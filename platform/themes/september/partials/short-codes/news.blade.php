<section class="section--homepage home-blog">
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
        <div class="section__content">
            <div class="row">
                @foreach($posts as $post)
                    <div class="col-xl-4 col-lg-4 col-12">
                        <article class="post">
                            <div class="post__wrapper">
                                <div class="post__thumbnail">
                                    <a class="post__overlay" href="{{ $post->url }}"></a>
                                    <img src="{{ RvMedia::getImageUrl($post->image, 'medium', false, RvMedia::getDefaultImage()) }}" alt="{{ $post->name }}" />
                                </div>
                                <div class="post__content">
                                    <div class="post__header">
                                        <h4 class="post__title">
                                            <a href="{{ $post->url }}">{{ $post->name }}</a>
                                        </h4>
                                        <span> {{ $post->created_at->translatedFormat('d M, Y') }}</span>
                                    </div>
                                    <p>{{ Str::words($post->description, 35) }}</p>
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
