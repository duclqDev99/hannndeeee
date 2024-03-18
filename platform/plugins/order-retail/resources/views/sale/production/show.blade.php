@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @php
        $saleStep = get_action(
            \Botble\OrderStepSetting\Enums\ActionEnum::RETAIL_SALE_SEND_PRODUCTION,
            $production->order->id,
        );
        $hgfAdminStep = get_action(
            \Botble\OrderStepSetting\Enums\ActionEnum::HGF_ADMIN_CONFIRM_PRODUCTION,
            $production->order->id,
        );

        $paramCode = str_replace('#', '', $production->order->code);
        $route = route('retail.sale.purchase-order.index') . '?order_code=' . $paramCode;
    @endphp

    <div class="col-10 mx-auto">
        <div class="card position-relative">
            <div class="bg-white card-header position-sticky top-0" style="z-index: 100">
                <div class="w-100 d-flex align-items-center justify-content-between ">
                    <h3 class="card-title">Thông tin đơn đặt hàng <a href="" class="fw-bold"></a></h3>
                    {{-- {{$production->order->order->code}} --}}
                    {{-- @if ($accountantStep->status == 'confirmed')
                        <span class="badge bg-success text-white">Đã thanh toán</span>
                    @else
                        <span class="badge bg-secondary text-white">Chưa thanh toán</span>
                    @endif --}}
                    {{-- <div class="d-flex gap-3">    
                    @if (auth()->user()->hasPermission('purchase-order.requesting_approve'))
                        
                        @if ($saleStep->status == 'pending' || $saleStep->status == 'refused')
                            <button class="dropdown-item d-inline-flex gap-2 align-items-center update_step_btn"
                                data-action="{{ Botble\OrderStepSetting\Enums\ActionEnum::RETAIL_SALE_REQUESTING_APPROVE_ORDER }}"
                                data-status="sended" data-type="next" data-order-id="{{ $item->id }}">
                                <span class="icon-tabler-wrapper">
                                    <i class="fa-solid fa-share-from-square"></i>
                                </span>
                                Gửi phê duyệt
                            </button>
                        @endif
                    @endif

                    <button class="btn btn-primary" id="submit-btn">Lưu</button>
                </div> --}}
                </div>
            </div>
            <div class="card-body overflow-auto">
                <div class="mb-3">
                    <div class="mb-1">
                        <span class="fw-bold">Số YCSX:</span>
                        <a href="{{ $route }}">{{ $production->order->code }}</a>
                    </div>
                    <div class="mb-1">
                        <span class="fw-bold">Khách hàng:</span>
                        <span>{{ $production->order->customer_name }}</span>
                    </div>
                    <div class="mb-1">
                        <span class="fw-bold">SĐT:</span>
                        <span>{{ $production->order->customer_phone }}</span>
                    </div>
                    <div class="mb-1">
                        <span class="fw-bold">Ngày cần hàng:</span>
                        <span>{{ \Carbon\Carbon::parse($production->order->expected_date)->format('d-m-Y') }}</span>
                    </div>
                    
                    <div class="mb-1">
                        <span class="fw-bold">Người đề xuất:</span>
                         {{ $saleStep->handler->name }},
                         {{ \Carbon\Carbon::parse($saleStep->handled_at)->format('d-m-Y, H:i') }}
                    </div>
                    <div class="mb-1">
                        @if ($hgfAdminStep->status == 'confirmed')
                            <span class="badge bg-success text-white">Đã xác nhận</span>
                            <span>
                                bởi {{ $hgfAdminStep->handler->name }}.
                                {{ \Carbon\Carbon::parse($hgfAdminStep->handled_at)->format('d-m-Y, H:i') }}
                            </span>
                        @else
                            <span class="badge bg-secondary text-white">Chưa thanh toán</span>
                        @endif
                    </div>

                </div>
                <table class="table table-bordered ">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Mã sản phẩm</th>
                            <th>Tên thành phẩm</th>
                            <th>Đơn vị tính</th>
                            <th width="90">Số lượng</th>
                            <th>Đơn giá</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody id="product-preview">
                        @foreach ($production->order->products as $index => $product)
                            <tr>
                                <td>
                                    {{ $index + 1 }}
                                </td>
                                <td>
                                    {{ $product->sku }}
                                </td>
                                <td>
                                    {{ $product->product_name }}
                                </td>
                              
                                <td>
                                    {{ $product->cal }}
                                </td>
                                <td class="text-center">
                                    <input name="products[${i}][qty]" readonly class="form-control form-control-sm"
                                        type="number" min="1" value="{{ $product->qty ??  0}}">
                                </td>
                                <td>
                                    {{ number_format($product->price) }}đ
                                </td>
                                <td>
                                    {{ number_format($product->price * $product->qty ??  0) }}đ
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="7" class="text-end" style="border-right: none !important">
                                <span class="uppercase">Tổng tiền:</span>
                            </td>
                            <td colspan="1" style="border-left: none !important">
                                <span class="fw-bold uppercase">{{ number_format($production->order->amount) }}đ</span>
                            </td>
                        </tr>
                        
                    </tbody>
                </table>
                <form id="edit-purchase-order-form">
                    @csrf
                    <div class="row price-group">


                        {{-- <div class="col-md-3">
                            <div class="mb-3 position-relative">
                                <label class="form-label required" for="price" readonly>
                                    Loại đơn
                                </label>

                                {!! $production->order->order->order_type->toHtml() !!}
                            </div>
                        </div> --}}

                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
