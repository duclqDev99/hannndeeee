@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @php
        $saleStep = get_action(
            \Botble\OrderStepSetting\Enums\ActionEnum::RETAIL_SALE_REQUESTING_APPROVE_ORDER,
            $order->id,
        );
        $hgfAdminStep = get_action(\Botble\OrderStepSetting\Enums\ActionEnum::HGF_ADMIN_CONFIRM_ORDER, $order->id);

        $paramCode = str_replace('#', '', $order->code);
        $route = route('hgf.admin.purchase-order.index') . '?order_code=' . $paramCode;

    @endphp

    <div class="col-10 mx-auto">
        <form method="POST" action="{{ route('hgf.admin.purchase-order.confirm') }}">
            @csrf
            <div class="card position-relative">
                <div class="bg-white card-header position-sticky top-0" style="z-index: 100">
                    <div class="w-100 d-flex align-items-center justify-content-between ">
                        <h3 class="card-title">Thông tin yêu cầu sản xuất <a href="" class="fw-bold"></a></h3>
                    </div>
                </div>
                <div class="card-body overflow-auto">
                    <div class="mb-3">
                        <div class="mb-1">
                            <span class="fw-bold">Số YCSX:</span>
                            <a href="{{ $route }}">{{ $order->code }}</a>
                        </div>
                        <div class="mb-1">
                            <span class="fw-bold">Khách hàng:</span>
                            <span>{{ $order->customer_name }}</span>
                        </div>
                        <div class="mb-1">
                            <span class="fw-bold">SĐT:</span>
                            <span>{{ $order->customer_phone }}</span>
                        </div>
                        <div class="mb-1">
                            <span class="fw-bold">Ngày cần hàng:</span>
                            <span>{{ \Carbon\Carbon::parse($order->expected_date)->format('d-m-Y') }}</span>
                        </div>

                        <div class="mb-1">
                            <span class="fw-bold">Người đề xuất:</span>
                            {{ $saleStep->handler->name }},
                            {{ \Carbon\Carbon::parse($saleStep->handled_at)->format('d-m-Y, H:i') }}
                        </div>

                        <div class="mb-1">
                            <span class="fw-bold">Ghi chú:</span>
                            {{ $saleStep->note }}
                        </div>


                        <div class="mb-1">
                            <span class="fw-bold"> Loại đơn:</span>
                            {!! $order->order_type->toHtml() !!}
                        </div>
                        <div class="mb-1">
                            <span class="fw-bold">Trạng thái:</span>
                            @switch($hgfAdminStep->status)
                                @case('confirmed')
                                    <span class="badge bg-success text-white">Đã duyệt</span>
                                @break

                                @case('canceled')
                                    <span class="badge bg-warning text-white">Đã đề xuất chỉnh sửa</span>
                                @break

                                @case('pending')
                                    <span class="badge bg-info text-white">Chờ duyệt</span>
                                @break
                            @endswitch
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
                                @if ($hgfAdminStep->status == 'cancelled')
                                    <th>Giá đề xuất từ HGF</th>
                                @endif
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody id="product-preview">
                            @php
                                $totalAmount = 0;
                            @endphp
                            @foreach ($order->products as $index => $product)
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
                                        {{ $product->sizes->count() ?? 0 }}
                                    </td>
                                    <td>
                                        {{ number_format($product->price) }}đ
                                    </td>
                                    @if ($hgfAdminStep->status == 'cancelled')
                                        {{ number_format($product->hgf_price) }}đ
                                    @endif
                                    <td>
                                        @php
                                            $totalAmount += $product->price * $product->sizes->count() ?? 0;
                                        @endphp
                                        {{ number_format($product->price * $product->sizes->count() ?? 0) }}đ
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="7" class="text-end" style="border-right: none !important">
                                    <span class="uppercase">Tổng tiền:</span>
                                </td>
                                <td colspan="1" style="border-left: none !important">
                                    <span class="fw-bold uppercase">{{ number_format($totalAmount) }}đ</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    @if ($hgfAdminStep->status == 'pending')
                        <div>
                            <input type="hidden" name="type" value="confirm">
                            <input type="hidden" name="order_id" value="{{$order->id}}">
                            {{-- <div class="d-flex gap-3 align-items-center pb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="confirm"
                                        value="confirm" checked>
                                    <label class="form-check-label" for="confirm" id="hide-collapse">
                                        Đồng ý với thông tin trên
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="cancel"
                                        value="cancel" data-bs-toggle="collapse" href="#showForm">
                                    <label class="form-check-label" for="cancel">
                                        Đề xuất chỉnh sửa
                                    </label>
                                </div>
                            </div> --}}
                            {{-- <div class="collapse" id="showForm">
                               
                                <input type="hidden" name="order_id" value="{{ $order->id }}">
                                <table class="table table-bordered ">
                                    <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>Mã sản phẩm</th>
                                            <th>Tên thành phẩm</th>
                                            <th>Size</th>
                                            <th>Đơn vị tính</th>
                                            <th width="90">Số lượng</th>
                                            <th>Đơn giá</th>
                                        </tr>
                                    </thead>
                                    <tbody id="product-preview">
                                        @foreach ($order->products as $index => $product)
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
                                                    {{ $product->size }}
                                                </td>
                                                <td>
                                                    {{ $product->cal }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $product->qty }}
                                                </td>
                                                <td class="text-center">
                                                    <input name="products[{{ $product->id }}]price_edit"
                                                        class="form-control form-control-sm" type="number" min="1"
                                                        value="{{ round($product->price) }}">    
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div> --}}
                            <div class="d-flex gap-3 align-items-center justify-content-end">
                                <button class="btn btn-primary" type="submit">Xác nhận</button>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </form>
    </div>
@endsection

@push('footer')
    <script>
        document.addEventListener("DOMContentLoaded", (event) => {
            $(document).on('click', '#hide-collapse', function() {
                $('#showForm').collapse('hide');
            })
        });
    </script>
@endpush
