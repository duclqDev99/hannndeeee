        <footer class="footer">
            <div class="container-fluid p-4 pb-0">
                <!-- Section: Links -->
                <section class="">
                    <!--Grid row-->
                    <div class="row">
                        <!--Grid column-->
                        <div class="col-lg-4 col-md-12 col-sm-12 col-12 mb-4 mb-md-0">
                            @if (is_plugin_active('widget-custom'))
                                {!! dynamic_sidebar_custom('footer_sidebar_custom_left') !!}
                            @endif
                            @if (theme_option('address') || theme_option('hotline') || theme_option('email'))
                                <aside class="widget widget--footer">
                                    <div class="widget__content">
                                        @if (theme_option('hotline'))
                                            <p><strong class="d-inline-block">{{ __('Hotline') }}:</strong>&nbsp;<span
                                                    class="d-inline-block">{{ theme_option('hotline') }}</span></p>
                                        @endif
                                        @if (theme_option('email'))
                                            <p><strong class="d-inline-block">{{ __('Email') }}:</strong>&nbsp;<span
                                                    class="d-inline-block">{{ theme_option('email') }}</span></p>
                                        @endif
                                    </div>
                                </aside>
                            @endif
                        </div>
                        <!--Grid column-->

                        <!--Grid column-->
                        <div class="col-lg-3 col-md-6 col-sm-6 col-6 mb-4 mb-md-0 widget-support">
                            @if (is_plugin_active('widget-custom'))
                                {!! dynamic_sidebar_custom('footer_sidebar_custom_center') !!}
                            @endif
                        </div>
                        <!--Grid column-->

                        <!--Grid column-->
                        <div class="col-lg-2 col-md-6 col-sm-6 col-6 mb-4 mb-md-0 widget-support">
                            @if (is_plugin_active('widget-custom'))
                                {!! dynamic_sidebar_custom('footer_sidebar_custom_right') !!}
                            @endif
                        </div>
                        <!--Grid column-->

                        <!--Grid column-->
                        <div class="col-lg-3 col-md-6 col-sm-6 col-6 mb-md-0 widget-support">
                            @if (is_plugin_active('widget-custom'))
                            {!! dynamic_sidebar_custom('footer_sidebar_custom_center_end') !!}
                        @endif
                        </div>
                        <!--Grid column-->
                    </div>
                    <!--Grid row-->
                </section>
                <!-- Section: Links -->

                <hr class="mb-4" />

                <!-- Section: Social media -->
                <section class="text-center">
                    @if (theme_option('social_links'))
                            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12 mx-auto">
                                <aside class="widget widget--footer">
                                    <h3 class="widget__title">{{ __('Connect with us') }}</h3>
                                    <ul class="list--social">
                                        @foreach (json_decode(theme_option('social_links'), true) as $socialLink)
                                            @if (count($socialLink) == 3)
                                                <li>
                                                    <a href="{{ $socialLink[2]['value'] }}"
                                                        title="{{ $socialLink[0]['value'] }}">
                                                        <i
                                                            class="{{ Str::contains($socialLink[1]['value'], 'icon-') ? 'feather icon ' : '' }}{{ str_replace('fab ', 'fa ', $socialLink[1]['value']) }}"></i>
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </aside>
                            </div>
                        @endif
                </section>
                <!-- Section: Social media -->
            </div>

        </footer>
        <div class="site-mask"></div>
        <div class="panel--search" id="site-search"><a class="panel__close" href="#"><i
                    class="feather icon icon-x"></i></a>
            <div class="container">
                <form class="form--primary-search"
                    action="{{ is_plugin_active('ecommerce') ? route('public.products') : (is_plugin_active('blog') ? route('public.search') : '#') }}"
                    method="GET">
                    <input class="form-control" name="q" type="text"
                        value="{{ BaseHelper::stringify(request()->query('q')) }}"
                        placeholder="{{ __('Search for') }}...">
                    <button><i class="fa fa-search"></i></button>
                </form>
            </div>
        </div>
        @if (is_plugin_active('ecommerce') && EcommerceHelper::isCartEnabled())
            <aside class="panel--sidebar" id="panel-cart">
                <div class="panel__header">
                    <h4>{{ __('Shopping Cart') }}</h4><span class="panel__close"></span>
                </div>
                <div class="panel__content">
                    {!! Theme::partial('cart-panel') !!}
                </div>
            </aside>
        @endif
        <aside class="panel--sidebar" id="panel-menu">
            <div class="panel__header">
                <h4>{{ __('Menu') }}</h4><span class="panel__close"></span>
            </div>
            <div class="panel__content">
                {!! Menu::renderMenuLocation('main-menu', [
                    'options' => ['class' => 'menu menu--mobile'],
                    'view' => 'main-menu',
                ]) !!}
            </div>
        </aside>

        <script>
            window.trans = {
                "No reviews!": "{{ __('No reviews!') }}",
                "days": "{{ __('days') }}",
                "hours": "{{ __('hours') }}",
                "mins": "{{ __('mins') }}",
                "sec": "{{ __('sec') }}",
            };

            window.siteUrl = "{{ route('public.index') }}";
        </script>

        {!! Theme::footer() !!}

        {!! Theme::place('footer') !!}

        @if (session()->has('success_msg') ||
                session()->has('error_msg') ||
                (isset($errors) && $errors->count() > 0) ||
                isset($error_msg))
            <script type="text/javascript">
                window.onload = function() {
                    @if (session()->has('success_msg'))
                        window.showAlert('alert-success', '{{ session('success_msg') }}');
                    @endif

                    @if (session()->has('error_msg'))
                        window.showAlert('alert-danger', '{{ session('error_msg') }}');
                    @endif

                    @if (isset($error_msg))
                        window.showAlert('alert-danger', '{{ $error_msg }}');
                    @endif

                    @if (isset($errors))
                        @foreach ($errors->all() as $error)
                            window.showAlert('alert-danger', '{!! $error !!}');
                        @endforeach
                    @endif
                };
            </script>
        @endif
        </body>

        </html>


