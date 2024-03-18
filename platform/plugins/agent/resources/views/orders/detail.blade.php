@extends(BaseHelper::getAdminMasterLayoutTemplate())
@push('header')
@endpush

@section('content')
    <div class="row row-cards">
        <div class="col-md-9">
            <div class="card mb-3">
                <div class="card-header justify-content-between">
                    <h4 class="card-title">
                        Thông tin đơn hàng #{{$order->code}}
                    </h4>

                    <span class="badge bg-success text-success-fg d-flex align-items-center gap-1">
                        <span class="icon-tabler-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-shopping-cart"
                                width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                <path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                <path d="M17 17h-11v-14h-2"></path>
                                <path d="M6 5l14 1l-1 7h-13"></path>
                            </svg>


                        </span>
                        Hoàn thành
                    </span>
                </div>


                <table class="table table-vcenter card-table">
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td style="width: 80px">
                                    <img src="http://127.0.0.1:8000/vendor/core/core/base/images/placeholder.png"
                                        alt="TẤT NGẮN XÁM/TRẮNG (ĐÔI)">
                                </td>
                                <td style="width: 45%">
                                    <div class="d-flex align-items-center flex-wrap">
                                        <span style="color:blue">
                                            {{$product?->reference?->name ?? ''}}
                                        </span>

                                        <p class="mb-0">(SKU:
                                            <strong>{{$product?->reference?->sku ?? ''}}</strong>)
                                        </p>
                                    </div>

                                    <div>
                                        <small>(
                                            @foreach ($product?->reference?->variationProductAttributes as $attribute)
                                                @if ($attribute?->color)
                                                    {{ 'Màu: ' . $attribute->title }}
                                                @endif
                                            @endforeach

                                            @foreach ($product?->reference?->variationProductAttributes as $attribute)
                                                @if (!$attribute->color)
                                                    {{ 'Size: ' . $attribute->title }}
                                                @endif
                                            @endforeach
                                        )</small>
                                    </div>
                                </td>
                                <td>
                                    {{ number_format($product?->reference?->price, 0, '.', ',') . '₫'}}
                                </td>
                                <td>
                                    x
                                </td>
                                <td>
                                    {{$product?->total}}
                                </td>
                                <td>
                                    {{ number_format($product?->reference?->price * $product?->total, 0, '.', ',') . '₫'}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>


                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <table class="table table-vcenter card-table table-borderless text-end">
                                <tbody>
                                    <tr>
                                        <td colspan="2">
                                            <hr class="my-0">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Số lượng
                                        </td>
                                        <td>
                                            {{$totalOrder}}
                                        </td>
                                    </tr>
                                    <tr>

                                        <td>
                                            Số tiền đã thanh toán
                                        </td>
                                        <td>
                                            <span>{{ number_format($order?->amount, 0, '.', ',') . '₫'}}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="list-group list-group-flush">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                        <div class="text-uppercase">
                            <span class="icon-tabler-wrapper text-success">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-check"
                                    width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M5 12l5 5l10 -10"></path>
                                </svg>


                            </span>
                            Đơn hàng đã hoàn thành
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        Đại lý
                    </h4>
                </div>
                <div class="card-footer">
                    <h2>{{$agentName->name}}</h2>
                </div>
            </div>
        </div>
    </div>
@endsection
