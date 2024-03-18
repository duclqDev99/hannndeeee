<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Đăng nhập Quản trị viên</title>
    <link rel="stylesheet" href="/vendor/core/plugins/custom-login/css/login.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/css/intlTelInput.css">
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/intlTelInput.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js?hl=vi"></script>

    <style>
        #error-msg {
            color: red;
        }

        /* Chrome, Safari, Edge, Opera */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Firefox */
        input[type=number] {
            -moz-appearance: textfield;
        }
        .loader-container{
            display: flex;
            justify-content: center;
        }
        .loader-frame {
            z-index: 2;
            overflow: visible;
            height: fit-content;
            width: fit-content;
            display: flex;
            padding-bottom: 5px;
        }

        .logo-loading .cls-2.active {
            fill: none;
            stroke-dasharray: 500px;
            /*<-- Play with this number until it look cool */
            stroke: rgb(255, 255, 255);
            animation: load 15s infinite linear;
        }

        .eye.active {
            color: red;
            stroke: #ffffff;
            stroke-width: 2;
            animation: blinking 3s infinite;
            /* Thời gian nhấp nháy và lặp lại vô hạn */
        }

        @keyframes load {
            0% {
                stroke-dashoffset: 0px;
            }

            100% {
                stroke-dashoffset: 9000px;
                /* <-- This number should always be 10 times the number up there*/
            }
        }

        @keyframes blinking {

            0%,
            100% {
                fill: #000000;
                /* Màu ban đầu của đa giác */
                filter: none;
                /* Không có hiệu ứng mờ */
            }

            50% {
                fill: #C0C0C0;
                /* Màu khi nhấp nháy */
            }
        }
    </style>
</head>
<body>
    <section class="sign-phone">
        <div class="login" style="height: 100vh;">
            <div class="row">
                <div class="col-12">
                    <div class="phone">
                        <h1>Đăng nhập tài khoản</h1>
                        <a href="{{ route('public.index') }}">
                            @if (theme_option('logo'))
                                <img src="/images/logo-title.png"
                                    alt="{{ theme_option('site_title') }}" loading="lazy" />
                            @endif
                            <div class="loader-container">
                                <div class="loader-frame">
                                    <?xml version="1.0" encoding="UTF-8"?>
                                    <svg id="Layer_2" class="logo-loading" width="90" data-name="Layer 2" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 525.71 642.83">
                                        <defs>
                                            <style>
                                                .cls-1 {
                                                    stroke-width: 0px;
                                                    fill:#fff
                                                }
                            
                                                .cls-2 {
                                                    fill: #fff;
                                                    stroke: #fff;
                                                    stroke-miterlimit: 10;
                                                    stroke-width: 9px;
                                                }
                                            </style>
                                        </defs>
                                        <g id="Layer_1-2" data-name="Layer 1">
                                            <g>
                                                <path class="cls-2"
                                                    d="m467.3,206.33l6.22-198.22-150.22,89.78h-121.78L50.42,8.1l8,198.22L5.08,349.44l219.56,288.89h78.22l217.78-288-53.33-144Zm-159.23,413.11h-87.11l-72-161.78-126.22-114.67,62.22-123.56,21.33,5.33-27.56-172.44,123.56,75.56,62.22-12.44,64.89,11.56,124.44-74.67-29.33,176,21.33-7.11,60.44,121.78-123.56,116.44-74.67,160Z" />
                                                <polygon class="cls-1" points="110.6 109.22 127.78 214.7 171.04 148.92 110.6 109.22" />
                                                <polygon class="cls-1" points="414.69 108.63 397.99 214.19 354.43 148.61 414.69 108.63" />
                            
                                                <polygon class="cls-1 eye"
                                                    points="117.12 334.1 148.52 387.14 204.23 393.66 227.04 411.73 210.75 378.84 117.12 334.1" />
                                                <polygon class="cls-1 eye"
                                                    points="409.08 334.1 377.67 387.14 321.97 393.66 299.16 411.73 315.45 378.84 409.08 334.1" />
                                                <polygon class="cls-1"
                                                    points="224.08 524.4 231.42 554.66 262.86 579.55 295.13 553.24 302.25 524.4 264.52 534.16 224.08 524.4" />
                                            </g>
                                        </g>
                                    </svg>
                            
                                </div>
                            </div>
                        </a>
                        

                        <div class="form">
                            <div class="tab-content">

                                <div id="tab-phone" style="display: block;">
                                    <form action="{{ route('access.send-otp') }}" method="post" id="signin-form"
                                        class="signin-form">
                                        @csrf
                                        <input type="hidden" id="countryCode" name="countrycode"
                                            value="{{ $countryCode }}">
                                        <section class="login-type">
                                            <div class="alert alert-danger validate-login-msg"
                                                style="display: none; margin-top: 2rem">
                                            </div>
                                            <input id="phone" type="tel" name="phonenumber"
                                                placeholder="Nhập số điện thoại" value="{{ $phonenumber }}"
                                                required="true" />
                                            {{-- <div id="valid-msg" class="hide login-mgs"><i class="fas fa-check-circle"></i>&nbsp;Số điện thoại hợp lệ</div> --}}
                                            <div id="error-msg" class="hide login-mgs"></div>

                                            <p class="text-phone">
                                            </p>

                                            <div class="g-recaptcha" data-sitekey="{{ setting('captcha_site_key') }}">
                                            </div>
                                        </section>
                                        <div class="buttons">
                                            
                                            <button id="btn_next" type="submit">Tiếp tục</button>
                                        </div>
                                    </form>

                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
        <script></script>
    </section>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="/vendor/core/plugins/custom-login/js/login.js"></script>
</body>

</html>
