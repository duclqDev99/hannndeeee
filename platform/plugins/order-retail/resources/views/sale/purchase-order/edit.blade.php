@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @php
        $saleStep = get_action(
            \Botble\OrderStepSetting\Enums\ActionEnum::RETAIL_SALE_REQUESTING_APPROVE_ORDER,
            $order->id,
        );

        $paramCode = str_replace('#', '', $order->code);
        $route = route('retail.sale.purchase-order.index') . '?order_code=' . $paramCode;
    @endphp

    <div class="card position-relative">
        <div class="bg-white card-header position-sticky top-0" style="z-index: 100">
            <div class="w-100 d-flex align-items-center justify-content-between ">
                <h3 class="card-title">Thông tin yêu cầu sản xuất <a href="{{ $route }}"
                        class="fw-bold">{{ $order->code }}</a></h3>
                <div class="d-flex gap-3">
                    {{-- 
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
                    @endif --}}

                    <button class="btn btn-primary" id="submit-btn">Lưu</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form id="edit-purchase-order-form"
                action="{{ route('retail.sale.purchase-order.update', ['order' => $order->id]) }}" method="POST">
                @csrf
                <div class="row price-group">
                    <div class="col-md-3">
                        <div class="mb-3 position-relative">
                            <label class="form-label" for="product_sku">
                                Số YCSX
                            </label>
                            <input class="form-control" type="text" name="code" id="product_sku"
                                value="{{ $order->code }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3 position-relative">
                            <label class="form-label" for="product_name">
                                Tên khách hàng
                            </label>
                            <input class="form-control" type="text" name="customer_name" id="product_name"
                                value="{{ $order->customer_name }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3 position-relative">
                            <label class="form-label" for="price">
                                Số điện thoại
                            </label>
                            <div class="input-group input-group-flat">
                                <input class="form-control" type="number" name="customer_phone" id="product_quantity"
                                    value="{{ $order->customer_phone }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3 position-relative">
                            <label class="form-label required" for="price">
                                Loại đơn
                            </label>

                            <select class="form-select form-control" name="order_type" id="product_size"
                                value="{{ $order->order_type->getValue() }}">
                                <option value="">Chọn Loại đơn</option>
                                @foreach (\Botble\OrderRetail\Enums\OrderTypeEnum::labels() as $value => $label)
                                    <option value="{{ $value }}" @if ($value == $order->order_type->getvalue()) selected @endif>
                                        {{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="position-relative mb-3">
                            <label for="note" class="control-label required">Mô tả chi tiết</label>
                            <textarea class="form-control" placeholder="" data-counter="500" rows="3" name="note" cols="50"
                                id="note">{!! $order->note !!}</textarea>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="mb-3 position-relative">
                            <label class="form-label" for="sale_price">
                                Tổng tiền
                            </label>
                            <div class="input-group input-group-flat">
                                <input class="form-control" type="text" name="product_total_price"
                                    id="product_total_price" value="{{ number_format($order->amount) }}đ" readonly>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
            <div class="card image-preview" data-name="images" id="box-image">
                <div class="card-header">
                    <div class="w-100 d-flex align-items-center justify-content-between">
                        <div class="d-inline-flex align-items-center gap-3">
                            <h3 class="card-title">Thông tin sản phẩm</h3>
                            <p class="mb-0 text-danger" id="images-error-message" style="display: none">Vui lòng tải
                                ảnh mô tả sản phẩm!</p>
                        </div>
                        {{-- <button type="button" id="add-image-btn" class="btn btn-primary p-0">
                            <button class="btn btn-primary p-3 py-2" style="cursor: pointer">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </button> --}}
                    </div>
                </div>
                <div class="card-body" style="display: block">
                    <div id="product-files" class="d-none"></div>
                    <table class="table table-bordered table-vcenter">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Mã sản phẩm</th>
                                <th>Tên thành phẩm</th>
                                <th>Đơn vị tính</th>
                                <th width="90">Số lượng</th>
                                <th>Giá dự kiến</th>
                                <th>Thành tiền</th>
                                <th>Chi tiết</th>
                                {{-- <th>Tùy chọn</th> --}}
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
                                        {{ $product->cal }}
                                    </td>
                                   
                                    <td class="text-center">
                                        {{ $product->qty }}
                                    </td>
                                    <td>
                                        {{ number_format($product->price) }}đ
                                    </td>

                                    <td>
                                        {{ number_format($product->price * $product->qty) }}đ
                                    </td>
                                    <td>
                                        <a data-id="{{ $product->id }}" type="button" data-bs-toggle="modal"
                                            data-bs-target="#edit-product-modal" class="edit-product-btn">Xem chi
                                            tiết</a>
                                    </td>
                                    {{-- <td class="text-center">
                                        <a href="javascript:void(0)" class="text-decoration-none delete-product-item-btn"
                                            data-id="${item?.id}">
                                            <span class="icon-tabler-wrapper icon-sm icon-left"><svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler icon-tabler-x" width="24" height="24"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                    <path d="M18 6l-12 12"></path>
                                                    <path d="M6 6l12 12"></path>
                                                </svg>
                                            </span>
                                        </a>
                                    </td> --}}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <x-core::modal id="edit-product-modal" :title="'Chi tiết mặt hàng'" button-id="submit-edit-product-btn"
            data-load-form-url="{{ route('retail.sale.purchase-order.get-add-product-form') }}" :button-label="'Lưu'"
            :size="'xl'">
            <div class="edit-product-form-wrapper">

            </div>
        </x-core::modal>
    @endsection
