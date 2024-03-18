@switch($type)
    @case('youtube')
    @case('vimeo')
        <div class="embed-responsive embed-responsive-16by9 mb30">
            {!! Html::tag('iframe', '', $data)->toHtml() !!}
        </div>
        @break
    @case('tiktok')
        <blockquote
            class="tiktok-embed"
            cite="{{ $data['url'] }}"
            data-video-id="{{ $data['video_id'] }}"
            style="max-width: 605px; min-width: 325px; margin-bottom: 20px; border: none;">
            <section></section>
        </blockquote>
        @break
    @case('video')
        <video {!! Html::attributes(Arr::except(['url', 'extension'], $data)) !!} controls>
            <source src="{{ $data['url'] }}" type="video/{{ $data['extension'] }}">
        </video>
@endswitch
