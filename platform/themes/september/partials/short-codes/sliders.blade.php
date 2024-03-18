@if (count($sliders) > 0)
    <div class="owl-slider owl-carousel carousel--nav inside" data-owl-auto="true" data-owl-loop="true" data-owl-speed="5000" data-owl-gap="0" data-owl-nav="true" data-owl-dots="false" data-owl-item="1" data-owl-item-xs="1" data-owl-item-sm="1" data-owl-item-md="1" data-owl-item-lg="1" data-owl-duration="1000" data-owl-mousedrag="on">
        @php $sliders->loadMissing('metadata'); @endphp

        @foreach($sliders as $slider)
            @php
                $slider->loadMissing('metadata');
                $buttonText = $slider->getMetaData('button_text', true);
            @endphp
            <div class="slider-item">
                @php
                    $tabletImage = $slider->getMetaData('tablet_image', true);
                    $mobileImage = $slider->getMetaData('mobile_image', true);
                @endphp
                <picture>
                    <source srcset="{{ RvMedia::getImageUrl($slider->image, null, false, RvMedia::getDefaultImage()) }}" media="(min-width: 1200px)" />
                    <source srcset="{{ RvMedia::getImageUrl($tabletImage ?: $slider->image, null, false, RvMedia::getDefaultImage()) }}" media="(min-width: 768px)" />
                    <source srcset="{{ RvMedia::getImageUrl($mobileImage ?: $tabletImage ?: $slider->image, null, false, RvMedia::getDefaultImage()) }}" media="(max-width: 767px)" />
                    <img src="{{ RvMedia::getImageUrl($slider->image, null, false, RvMedia::getDefaultImage()) }}" alt="{{ $slider->title }}" loading="eager"/>
                </picture>

                @if ($slider->title || $slider->description)
                    <div class="slider__content">
                        <div class="slider__content__wrapper">
                            <div class="slider__content__wrapper__content">
                                @if ($slider->title)
                                    <h2 class="staggered-animation animated fadeInDown" data-animation="fadeInDown" data-animation-delay="0.3s">{{ $slider->title }}</h2>
                                @endif
                                @if ($slider->description)
                                    <p class="staggered-animation animated fadeInUp" data-animation="fadeInUp" data-animation-delay="0.4s">{{ $slider->description }}</p>
                                @endif
                                @if ($slider->link)
                                    <a class="btn--custom btn--rounded staggered-animation animated fadeInUp" href="{{ $slider->link }}" data-animation="fadeInUp" data-animation-delay="0.5s">{!! BaseHelper::clean($buttonText ?: __('Shop Now')) !!}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif
