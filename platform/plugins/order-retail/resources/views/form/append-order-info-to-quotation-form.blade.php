@php
    $sizes = [
        's' => 'S',
        'm' => 'M',
        'l' => 'L',
        'xl' => 'XL',
        '2xl' => '2xL',
        'freesize' => 'FreeSize',
    ];
@endphp

<div class="row price-group">
    <input class="detect-schedule d-none" name="sale_type" type="hidden" value="0">

    <div class="col-md-4">
        <div class="mb-3 position-relative">
            <label class="form-label " for="product_sku">
                Tên khách hàng
            </label>
            <input class="form-control" type="text" value="{{ $order->customer_name }}" readonly>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3 position-relative">
            <label class="form-label " for="product_name">
                Số điện thoại
            </label>
            <input class="form-control" type="text" value="{{ $order->customer_phone }}" readonly>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3 position-relative">
            <label class="form-label " for="sale_price">
                Ngày cần hàng
            </label>
            <div class="input-group input-group-flat">
                <input class="form-control" type="text" value="{{ $order->expected_date }}" readonly>
            </div>
        </div>
    </div>

    {{-- <div class="col-md-6">
        <div class="mb-3 position-relative">
            <label class="form-label " for="product_sku">
                Giá sản xuất
            </label>
            <input class="form-control" type="text" name="purchase_order_amount" id="product_sku" readonly
                value="{{ $order->amount }}">
        </div>
    </div> --}}
    {{-- <div class="col-md-6">
        <div class="mb-3 position-relative">
            <label class="form-label required" for="product_name">
                Giá bán
            </label>
            <input placeholder="Vui lòng nhập giá bán lớn hơn giá SX" class="form-control" type="number"
                name="quotation_amount" id="quotation_amount" value="{{ $order?->quotation_amount }}">
        </div>
    </div> --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <label class="form-label " for="product_sku">
                    Thông tin mặt hàng
                </label>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-vcenter">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Mã sản phẩm</th>
                            <th>Tên thành phẩm</th>
                            <th width="90">Số lượng</th>
                            <th>Đơn vị tính</th>
                            <th>Giá sản xuất</th>
                            <th>Giá bán</th>
                        </tr>
                    </thead>
                    <div>
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

                                    <td class="text-center">
                                        {{ $product->qty ??  0}}
                                    </td>
                                    <td>
                                        {{ $product->cal }}
                                    </td>
                                   
                                    <td>
                                        {{ number_format($product->price) }}đ
                                    </td>
                                    <td>
                                        <input name="products[{{ $product->id }}][price]"
                                            class="form-control form-control-sm" type="hidden"
                                            value="{{ $product->price }}">
                                        <input name="products[{{ $product->id }}][quotation_price]"
                                            class="form-control form-control-sm" type="number" min="1"
                                            value="{{ $product->quotation_price == 0 ? $product->price : $product->quotation_price }}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </div>
                </table>
            </div>
        </div>
    </div>
</div>
