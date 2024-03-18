@if (count($sliders) > 0)
    <div class="slider-banner">
        <div class="swiper mySwiper"">
            @php $sliders->loadMissing('metadata'); @endphp
            <div class="swiper-wrapper">
                @foreach($sliders as $slider)
                    @php
                        $slider->loadMissing('metadata');
                        $buttonText = $slider->getMetaData('button_text', true);
                    @endphp
                    <div class="swiper-slide">
                        @php
                            $tabletImage = $slider->getMetaData('tablet_image', true);
                            $mobileImage = $slider->getMetaData('mobile_image', true);

                            $extension = strtolower(pathinfo($slider->image, PATHINFO_EXTENSION));
                        @endphp

                        @if(in_array($extension, ['jpg', 'jpeg', 'png', 'gif']))
                            <picture>
                                <source srcset="{{ RvMedia::getImageUrl($slider->image, null, false, RvMedia::getDefaultImage()) }}" media="(min-width: 1200px)" />
                                <source srcset="{{ RvMedia::getImageUrl($tabletImage ?: $slider->image, null, false, RvMedia::getDefaultImage()) }}" media="(min-width: 768px)" />
                                <source srcset="{{ RvMedia::getImageUrl($mobileImage ?: $tabletImage ?: $slider->image, null, false, RvMedia::getDefaultImage()) }}" media="(max-width: 767px)" />
                                <img src="{{ RvMedia::getImageUrl($slider->image, null, false, RvMedia::getDefaultImage()) }}" alt="{{ $slider->title }}" loading="eager"/>
                            </picture>
                        @elseif(in_array($extension, ['mp4', 'avi', 'mov', 'wmv']))
                            <video width="100%" height="100%" loop autoplay muted style="object-fit: fill;">
                                <source src="{{ RvMedia::getImageUrl($slider->image, null, false, RvMedia::getDefaultImage() )}}" type="video/mp4">
                            </video>
                        @endif

        
                        @if ($slider->title || $slider->description)
                            <div class="slider__content">
                                <div class="slider__content__wrapper">
                                    <div class="slider__content__wrapper__content">
                                        @if ($slider->title)
                                            <h2 class="fadeInDown" data-animation="fadeInDown" data-animation-delay="0.3s">{{ $slider->title }}</h2>
                                        @endif
                                        @if ($slider->description)
                                            <p class="fadeInUp" data-animation="fadeInUp" data-animation-delay="0.4s">{{ $slider->description }}</p>
                                        @endif
                                        @if ($slider->link)
                                            <a class="btn-seemore fadeInUp" href="{{ $slider->link }}" data-animation="fadeInUp" data-animation-delay="0.5s">{!! BaseHelper::clean($buttonText ?: __('Shop Now')) !!}</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
            <div class="autoplay-progress">
                <svg viewBox="0 0 48 48">
                    <circle cx="24" cy="24" r="20"></circle>
                </svg>
                <span></span>
            </div>
        </div>
    </div>
@endif
