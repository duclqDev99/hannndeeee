<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=1" name="viewport"/>

        <meta name="csrf-token" content="{{ csrf_token() }}">

        {!! BaseHelper::googleFonts('https://fonts.googleapis.com/css2?family=' . urlencode(theme_option('primary_font', 'Poppins')) . ':wght@400;500;600;700;900&display=swap') !!}

        <style>
            :root {
                --color-1st: {{ theme_option('primary_color', '#026e94') }};
                --primary-color: {{ theme_option('primary_color', '#026e94') }};
                --color-2nd: {{ theme_option('secondary_color', '#2c1dff') }};
                --secondary-color: {{ theme_option('secondary_color', '#2c1dff') }};
                --primary-font: '{{ theme_option('primary_font', 'Poppins') }}', sans-serif;
            }
        </style>

        {!! Theme::header() !!}
    </head>
    <body @if (BaseHelper::siteLanguageDirection() == 'rtl') dir="rtl" @endif>
        {!! apply_filters(THEME_FRONT_BODY, null) !!}
        <div id="alert-container"></div>
        <header class="header header--mobile">
            <nav class="navigation--mobile">
                <div class="navigation__left">
                    <a class="logo" href="{{ route('public.index') }}">
                        @if (theme_option('logo'))
                            <img src="{{ RvMedia::getImageUrl(theme_option('logo')) }}" alt="{{ theme_option('site_title') }}" height="60" loading="lazy"/>
                        @endif
                    </a>
                </div>
                <div class="navigation__right">
                    <div class="header__actions">
                        <a class="search-btn" href="#"><i class="feather icon icon-search"></i></a>
                        @if (is_plugin_active('ecommerce'))
                            <a href="{{ route('customer.login') }}"><i class="feather icon icon-user"></i></a>
                            @if (EcommerceHelper::isWishlistEnabled())
                                <a class="btn-shopping-cart btn-wishlist" href="{{ route('public.wishlist') }}"><i class="feather icon icon-heart"></i>@if (auth('customer')->check())<span>{{ auth('customer')->user()->wishlist()->count() }}</span> @else <span>{{ Cart::instance('wishlist')->count() }}</span>@endif</a>
                            @endif
                            @if (EcommerceHelper::isCartEnabled())
                                <a class="btn-shopping-cart panel-trigger" href="#panel-cart">
                                    <i class="feather icon icon-shopping-cart"></i><span>{{ Cart::instance('cart')->count() }}</span>
                                </a>
                            @endif
                        @endif
                        <a class="panel-trigger" href="#panel-menu"><i class="feather icon icon-menu"></i></a></div>
                </div>
            </nav>
        </header>
        <header class="header" data-sticky="{{ theme_option('enabled_sticky_header', 'no') == 'yes' ? 'true' : 'false' }}">
            <div class="top-header d-none d-md-block">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-md-6 col-lg-5">
                            <div class="d-flex align-items-center justify-content-center justify-content-md-start">
                                <ul class="contact-detail text-center text-lg-left">
                                    @if (theme_option('hotline'))
                                        <li><i class="feather icon icon-phone"></i>&nbsp;<span>{{ theme_option('hotline') }}</span></li>
                                    @endif
                                    @if (theme_option('email'))
                                        <li><i class="feather icon icon-mail"></i>&nbsp;<a href="mailto:{{ theme_option('email') }}">{{ theme_option('email') }}</a></li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-7">
                            <div class="d-flex align-items-center justify-content-center justify-content-md-end">
                                @if (is_plugin_active('language'))
                                    <div class="language-wrapper">
                                        {!! Theme::partial('language-switcher') !!}
                                    </div>
                                @endif
                                @if (is_plugin_active('ecommerce'))
                                    @php $currencies = get_all_currencies(); @endphp
                                    @if (count($currencies) > 1)
                                        <div class="language-wrapper choose-currency mr-3">
                                            <div class="dropdown">
                                                <button class="btn btn-secondary dropdown-toggle btn-select-language" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                    {{ get_application_currency()->title }}
                                                    <span class="feather icon icon-chevron-down"></span>
                                                </button>
                                                <ul class="dropdown-menu language_bar_chooser">
                                                    @foreach ($currencies as $currency)
                                                        <li>
                                                            <a href="{{ route('public.change-currency', $currency->title) }}" @if (get_application_currency_id() == $currency->id) class="active" @endif><span>{{ $currency->title }}</span></a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @endif
                                    <ul class="header_list">
                                        @if (!auth('customer')->check())
                                            <li><a href="{{ route('customer.login') }}"><i class="feather icon icon-user"></i>&nbsp;<span>{{ __('Login') }}</span></a></li>
                                        @else
                                            <li><a href="{{ route('customer.overview') }}"><i class="feather icon icon-user"></i>&nbsp;<span>{{ auth('customer')->user()->name }}</span></a></li>
                                            <li><a href="{{ route('customer.logout') }}"><i class="feather icon icon-lock"></i>&nbsp;<span>{{ __('Logout') }}</span></a></li>
                                        @endif

                                        @if (EcommerceHelper::isCompareEnabled())
                                            <li><a href="{{ route('public.compare') }}"><i class="feather icon icon-shuffle"></i>&nbsp;<span>{{ __('Compare') }} <span class="compare-count">(<span>{{ Cart::instance('compare')->count() }}</span>)</span></span></a></li>
                                        @endif
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <nav class="navigation hdt-container-full hdt-px-40 hdt-px-15">
                <div class="hdt-flex hdt-align-center hdt-justify-between hdt-spacing-15 md:hdt-px-15 hdt-px-10">
                    <div class="navigation__left">
                        <a class="logo" href="{{ route('public.index') }}">
                            @if (theme_option('logo'))
                                <img src="{{ RvMedia::getImageUrl(theme_option('logo')) }}" alt="{{ theme_option('site_title') }}" height="50" loading="lazy"/>
                            @endif
                        </a>
                    </div>
                    <div class="navigation__center">
                        {!!
                            Menu::renderMenuLocation('main-menu', [
                                'options' => ['class' => 'menu'],
                                'view'    => 'main-menu',
                            ])
                        !!}
                    </div>
                    <div class="navigation__right">
                        <div class="header__actions">
                            <a class="search-btn" href="#"><i class="feather icon icon-search"></i></a>
                            @if (is_plugin_active('ecommerce'))
                                @if (EcommerceHelper::isWishlistEnabled())
                                    <a class="btn-shopping-cart btn-wishlist" href="{{ route('public.wishlist') }}"><i class="feather icon icon-heart"></i>@if (auth('customer')->check())<span>{{ auth('customer')->user()->wishlist()->count() }}</span>@else <span>{{ Cart::instance('wishlist')->count() }}</span>@endif</a>
                                @endif
                                @if (EcommerceHelper::isCartEnabled())
                                    <a class="btn-shopping-cart panel-trigger" href="#panel-cart"><i class="feather icon icon-shopping-cart"></i><span>{{ Cart::instance('cart')->count() }}</span></a>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </nav>
        </header>
