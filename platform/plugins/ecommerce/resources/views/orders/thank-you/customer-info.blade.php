@php
    $orders = $order;
    $order = $order instanceof \Illuminate\Support\Collection ? $order->first() : $order;
    $userInfo = $order->address->id ? $order->address : $order->user;
@endphp

<div class="order-customer-info">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <h3> {{ __('Customer information') }}</h3>
                    @if ($userInfo->id)
                        @if ($userInfo->name)
                            <p>
                                <span class="d-inline-block">{{ __('Full name') }}:</span>
                                <span class="order-customer-info-meta">{{ $userInfo->name }}</span>
                            </p>
                        @endif

                        @if ($userInfo->phone)
                            <p>
                                <span class="d-inline-block">{{ __('Phone') }}:</span>
                                <span class="order-customer-info-meta">{{ $userInfo->phone }}</span>
                            </p>
                        @endif

                        @if ($userInfo->email)
                            <p>
                                <span class="d-inline-block">{{ __('Email') }}:</span>
                                <span class="order-customer-info-meta">{{ $userInfo->email }}</span>
                            </p>
                        @endif

                        @if ($order->full_address)
                            <p>
                                <span class="d-inline-block">{{ __('Address') }}:</span>
                                <span class="order-customer-info-meta">{{ $order->full_address }}</span>
                            </p>
                        @endif
                    @endif

                    @if (!empty($isShowShipping))
                        <p>
                            <span class="d-inline-block">{{ __('Shipping method') }}:</span>
                            <span class="order-customer-info-meta">{{ $order->shipping_method_name }} -
                                {{ format_price($order->shipping_amount) }}</span>
                        </p>
                    @endif

                    @if (is_plugin_active('payment') && $order->payment->id)
                        <p>
                            <span class="d-inline-block">{{ __('Payment method') }}:</span>
                            <span
                                class="order-customer-info-meta">{{ $order->payment->payment_channel->label() }}</span>
                        </p>
                        <p>
                            <span class="d-inline-block">{{ __('Payment status') }}:</span>
                            <span class="order-customer-info-meta"
                                style="text-transform: uppercase">{!! BaseHelper::clean($order->payment->status->toHtml()) !!}</span>
                        </p>

                        @if (
                            $order->payment->payment_channel->getValue() == 'bank_transfer' &&
                                $order->payment->status->getValue() != 'completed')
                            <p class="bank_transfer_note">
                                <span class="d-inline-block"><strong>Vui lòng quét QR để hoàn tất thanh toán</strong>
                                </span>
                            </p>
                        @endif
                    @endif
                </div>

                @if (
                    $order->payment->payment_channel->getValue() == 'bank_transfer' &&
                        $order->payment->status->getValue() != 'completed')
                    <div class="col d-flex align-items-center justify-content-center">
                        <div class="card h-100 w-100 border-0 ">
                            <div class="card-body d-flex align-items-center justify-content-center" style="flex-direction: column;" id="qr-wrapper">
                                <div class="text-center qr-scan-loading ">
                                    <div class="spinner-border text-info" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p>Đang tải mã QR...</p>
                                </div>
                                <img id="widget-img-qrcode" style="display: none;max-width: 260px;" class="img-fluid qr-scan" {{-- https://quantrinhahang.edu.vn/wp-content/uploads/2019/07/qr-code-la-gi.jpg --}}
                                    src="" alt="" />

                                <button type="button" class="btn download_qrcode" style="background-color: #1fa4d1;color: #fff;padding: 10px 15px;">{{__('Download QRcode')}}</button>

                                <div style="display: none" class="success-checkmark">
                                    <div class="check-icon">
                                        <span class="icon-line line-tip"></span>
                                        <span class="icon-line line-long"></span>
                                        <div class="icon-circle"></div>
                                        <div class="icon-fix"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    {!! apply_filters('ecommerce_thank_you_customer_info', null, $order) !!}
</div>

@if ($tax = $order->taxInformation)
    <div class="order-customer-info">
        <h3> {{ __('Tax information') }}</h3>
        <p>
            <span class="d-inline-block">{{ __('Company name') }}:</span>
            <span class="order-customer-info-meta">{{ $tax->company_name }}</span>
        </p>

        <p>
            <span class="d-inline-block">{{ __('Company tax code') }}:</span>
            <span class="order-customer-info-meta">{{ $tax->company_tax_code }}</span>
        </p>

        <p>
            <span class="d-inline-block">{{ __('Company email') }}:</span>
            <span class="order-customer-info-meta">{{ $tax->company_email }}</span>
        </p>

        <p>
            <span class="d-inline-block">{{ __('Company address') }}:</span>
            <span class="order-customer-info-meta">{{ $tax->company_address }}</span>
        </p>
    </div>
@endif

@push('footer')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        document.addEventListener('DOMContentLoaded', function() {

            const getQrUrl = "{{ route('order-transactions.send-request-payment', $order->id) }}";
            const listenerStatusPaymentUrl = "/api/v1/order-transactions-client/notifications/{{ $order->id }}";

            $.ajax({
                url: getQrUrl,
                method: "POST",
                success: res => {
                    if (!res.error_code) {
                        $('.qr-scan')
                            .attr('src', res?.data?.qr_image)
                            .show();
                        $('.qr-scan-loading').hide();
                    }
                },
                error: res => console.log(res)
            });

            let source = new EventSource(listenerStatusPaymentUrl);
            const qrChannel = new BroadcastChannel('QR-customer-channel');
            const qrSuccessChannel = new BroadcastChannel('QR-customer-channel-success');

            // qrChannel.onmessage = (event) => {
            //     console.log(event)
            //     console.log('qrChannel')
            //     $('.qr-scan')
            //         .attr('src', event.data.qr_image)
            //         .show();
            //     $('.qr-scan-loading ').hide();

            // }

            source.onmessage = function(event) {
                const data = JSON.parse(event.data);
                if (data.error_code == 0) {
                    source.close();

                    source.close();
                    let modalQR = document.querySelector('#confirm-payment-modal .modal-body');
                    console.log('Đã thanh toán');
                    $('.btn-trigger-confirm-payment').remove();
                    let dataQR = {
                        'success': 1
                    }
                    qrSuccessChannel.postMessage(dataQR)
                    $('.qr-scan').remove();
                    $('.bank_transfer_note').remove();
                    $('.success-checkmark').show();
                    //Đổi trạng thái đơn thành success
                    $('.qr-scan-loading ').hide();
                    $('.checkout-content-wrap').find('.badge').removeClass('bg-warning');
                    $('.checkout-content-wrap').find('.badge').addClass('bg-success');
                    $('.checkout-content-wrap').find('.badge').text('ĐÃ HOÀN THÀNH');
                    $(body).remove();
                }
            };

            source.onerror = function(event) {};


            //Download img QRcode
            $('.download_qrcode').on('click', function(event){
                event.preventDefault();
                event.stopPropagation();
                
                let srcQR = $('#widget-img-qrcode').attr('src')

                if(srcQR !== ''){
                    var a = document.createElement('a');
                    a.href = srcQR;
                    a.download = 'qr-image.png';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                }
            })
        });
    </script>
@endpush

<style>
    /**
 * Extracted from: SweetAlert
 * Modified by: Istiak Tridip
 */
    .success-checkmark {
        width: 80px;
        height: 115px;
        margin: 0 auto;

        .check-icon {
            width: 80px;
            height: 80px;
            position: relative;
            border-radius: 50%;
            box-sizing: content-box;
            border: 4px solid #4CAF50;

            &::before {
                top: 3px;
                left: -2px;
                width: 30px;
                transform-origin: 100% 50%;
                border-radius: 100px 0 0 100px;
            }

            &::after {
                top: 0;
                left: 30px;
                width: 60px;
                transform-origin: 0 50%;
                border-radius: 0 100px 100px 0;
                animation: rotate-circle 4.25s ease-in;
            }

            &::before,
            &::after {
                content: '';
                height: 100px;
                position: absolute;
                background: #FFFFFF;
                transform: rotate(-45deg);
            }

            .icon-line {
                height: 5px;
                background-color: #4CAF50;
                display: block;
                border-radius: 2px;
                position: absolute;
                z-index: 10;

                &.line-tip {
                    top: 46px;
                    left: 14px;
                    width: 25px;
                    transform: rotate(45deg);
                    animation: icon-line-tip 0.75s;
                }

                &.line-long {
                    top: 38px;
                    right: 8px;
                    width: 47px;
                    transform: rotate(-45deg);
                    animation: icon-line-long 0.75s;
                }
            }

            .icon-circle {
                top: -4px;
                left: -4px;
                z-index: 10;
                width: 80px;
                height: 80px;
                border-radius: 50%;
                position: absolute;
                box-sizing: content-box;
                border: 4px solid rgba(76, 175, 80, .5);
            }

            .icon-fix {
                top: 8px;
                width: 5px;
                left: 26px;
                z-index: 1;
                height: 85px;
                position: absolute;
                transform: rotate(-45deg);
                background-color: #FFFFFF;
            }
        }
    }

    .payment-checkout-btn{
        display: block;
        margin: 0 auto;
        max-width: max-content;
        float: right;

        @media screen (max-width:767.98px) {
            float: unset;
        }
    }

    @keyframes rotate-circle {
        0% {
            transform: rotate(-45deg);
        }

        5% {
            transform: rotate(-45deg);
        }

        12% {
            transform: rotate(-405deg);
        }

        100% {
            transform: rotate(-405deg);
        }
    }

    @keyframes icon-line-tip {
        0% {
            width: 0;
            left: 1px;
            top: 19px;
        }

        54% {
            width: 0;
            left: 1px;
            top: 19px;
        }

        70% {
            width: 50px;
            left: -8px;
            top: 37px;
        }

        84% {
            width: 17px;
            left: 21px;
            top: 48px;
        }

        100% {
            width: 25px;
            left: 14px;
            top: 45px;
        }
    }

    @keyframes icon-line-long {
        0% {
            width: 0;
            right: 46px;
            top: 54px;
        }

        65% {
            width: 0;
            right: 46px;
            top: 54px;
        }

        84% {
            width: 55px;
            right: 0px;
            top: 35px;
        }

        100% {
            width: 47px;
            right: 8px;
            top: 38px;
        }
    }
</style>
