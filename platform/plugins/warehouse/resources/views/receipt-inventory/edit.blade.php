@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
    <div class="max-width-1200" id="main-order-content">
        <div class="ui-layout">
            <div class="flexbox-layout-sections">
                <div class="flexbox-layout-section-primary mt20">
                    <div class="ui-layout__item">
                        <form action="{{ route('receipt-inventory.create.stock') }}" method="post">
                            @csrf
                            <input type="text" name="proposal_code" value="{{ $receipt[0]->proposal_code }}" hidden>
                            <div class="wrapper-content">
                                <div class="pd-all-20">
                                    <div class="flexbox-grid-default">
                                        <div class="flexbox-auto-right mr5">
                                            <label class="title-product-main text-no-bold">{{ __('Phiếu nhập kho') }}
                                                {{ $receipt[0]->proposal_code }} -
                                                {{ $receipt[0]->materials->name }}</label>
                                        </div>
                                    </div>
                                    <div class="mt20">
                                        @if ($receipt[0]->expected_date)
                                            <svg class="svg-next-icon svg-next-icon-size-16 next-icon--right-spacing-quartered text-info"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                enable-background="new 0 0 24 24">
                                                <g>
                                                    <path
                                                        d="M20.2 1H3.9C2.3 1 1 2.3 1 3.9v16.9C1 22 2.1 23 3.4 23h17.3c1.3 0 2.3-1 2.3-2.3V3.9C23 2.3 21.8 1 20.2 1zM20 4v11h-2.2c-1.3 0-2.8 1.5-2.8 2.8v1c0 .3.2.2-.1.2H8.2c-.3 0-.2.1-.2-.2v-1C8 16.5 6.7 15 5.3 15H4V4h16zM10.8 14.7c.2.2.6.2.8 0l7.1-6.9c.3-.3.3-.6 0-.8l-.8-.8c-.2-.2-.6-.2-.8 0l-5.9 5.7-2.4-2.3c-.2-.2-.6-.2-.8 0l-.8.8c-.2.2-.2.6 0 .8l3.6 3.5z">
                                                    </path>
                                                </g>
                                            </svg>
                                            <strong
                                                class="ml5 text-info">{{ trans('plugins/ecommerce::order.completed') }}</strong>
                                        @else
                                            <svg class="svg-next-icon svg-next-icon-size-16 text-warning"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"
                                                enable-background="new 0 0 16 16">
                                                <g>
                                                    <path
                                                        d="M13.9130435,0 L2.08695652,0 C0.936347826,0 0,0.936347826 0,2.08695652 L0,14.2608696 C0,15.2194783 0.780521739,16 1.73913043,16 L14.2608696,16 C15.2194783,16 16,15.2194783 16,14.2608696 L16,2.08695652 C16,0.936347826 15.0636522,0 13.9130435,0 L13.9130435,0 Z M13.9130435,2.08695652 L13.9130435,10.4347826 L12.173913,10.4347826 C11.2153043,10.4347826 10.4347826,11.2153043 10.4347826,12.173913 L10.4347826,12.8695652 C10.4347826,13.0615652 10.2789565,13.2173913 10.0869565,13.2173913 L5.2173913,13.2173913 C5.0253913,13.2173913 4.86956522,13.0615652 4.86956522,12.8695652 L4.86956522,12.173913 C4.86956522,11.2153043 4.08904348,10.4347826 3.13043478,10.4347826 L2.08695652,10.4347826 L2.08695652,2.08695652 L13.9130435,2.08695652 L13.9130435,2.08695652 Z">
                                                    </path>
                                                </g>
                                            </svg>
                                            <strong
                                                class="ml5 text-warning">{{ trans('plugins/ecommerce::order.uncompleted') }}</strong>
                                        @endif
                                    </div>
                                </div>
                                <div class="pd-all-20 p-none-t border-top-title-main">
                                    <div class="table-wrap">
                                        <table class="table-order table-divided">
                                            <tbody>
                                                @foreach ($receipt as $orderProduct)
                                                    <tr class="item__product">
                                                        <td class="width-60-px min-width-60-px vertical-align-t">
                                                            <div class="wrap-img">
                                                                <img class="thumb-image thumb-image-cartorderlist"
                                                                    src="{{ RvMedia::getImageUrl($orderProduct->materials->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                                    alt="{{ $orderProduct->materials->name }}">
                                                            </div>
                                                        </td>

                                                        <td class="pl5 p-r5 min-width-200-px">
                                                            <a class="text-underline hover-underline pre-line"
                                                                href="{{ Auth::user()->hasPermission('material.edit') ? route('material.edit', $orderProduct->materials->id) : '#' }}"
                                                                title="{{ $orderProduct->materials->name }}"
                                                                target="_blank">
                                                                {{ $orderProduct->materials->name }}
                                                            </a>
                                                        </td>
                                                        <td class="pl5 p-r5 text-end">
                                                            <div class="inline_block">
                                                                <span>{{ format_price($orderProduct->price_import) }}</span>
                                                            </div>
                                                        </td>
                                                        <td class="pl5 p-r5 text-center">x</td>
                                                        <td class="pl5 p-r5">
                                                            <span>{{ $orderProduct->quantity }}</span>
                                                        </td>
                                                        <td class="pl5 text-end">
                                                            <span
                                                                class="widget__total__price">{{ format_price($orderProduct->price_import * $orderProduct->quantity) }}</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                <tr>
                                                    <td colspan="6" class="text-end h5">
                                                        <strong>Tổng tiền: </strong> <span
                                                            class="widget__amount">{{ format_price($receipt[0]->amount) }}</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="pd-all-20 p-none-t">
                                    <div class="flexbox-grid-default">
                                        <div class="flexbox-auto-right p-r5 d-sm-flex">
                                            <div class="py-3 w-100">
                                                <label
                                                    class="text-title-field">{{ trans('plugins/ecommerce::order.note') }}</label>
                                                <textarea class="ui-text-area" name="description" rows="4" placeholder="{{ __('Ghi chú') }}" disabled>{{ $receipt[0]->description }}</textarea>
                                            </div>
                                        </div>
                                        <div class="flexbox-auto-right pl5">
                                            <div class="row">
                                                <div class="col-lg-6 col-sm-12">
                                                    <div class="py-3">
                                                        <label class="text-title-field">{{ __('Ngày dự kiến') }}</label>
                                                        <input type="date" class="form-control" name="expected_date"
                                                            value="{{ $receipt[0]->expected_date }}" disabled>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt10">
                                        <button class="btn btn-primary" type="submit">{{ __('Nhập kho') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
