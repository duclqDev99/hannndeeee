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
    <div class="col-md-3">
        <div class="mb-3 position-relative">
            <label class="form-label " for="product_sku">
                Tên khách hàng
            </label>
            <input class="form-control" type="text" 
                value="{{$order->customer_name}}" readonly>
        </div>
    </div>
    <div class="col-md-3">
        <div class="mb-3 position-relative">
            <label class="form-label " for="product_name">
                Số điện thoại
            </label>
            <input class="form-control" type="text"
            value="{{$order->customer_phone}}" readonly>
        </div>
    </div>
    <div class="col-md-3">
        <div class="mb-3 position-relative">
            <label class="form-label " for="sale_price">
                Ngày cần hàng
            </label>
            <div class="input-group input-group-flat">
                <input class="form-control" type="text"
                    value="{{$order->expected_date}}" readonly>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="mb-3 position-relative">
            <label class="form-label " for="product_sku">
                Giá sản xuất
            </label>
            <input class="form-control" type="text" name="purchase_order_amount" id="product_sku" readonly
                value="{{$order->amount}}">
        </div>
    </div>

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
                            <th>Giá dự kiến</th>
                            <th>Thành tiền</th>
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
        
                                <td class="text-center">
                                    <input name="products[${i}][qty]" readonly class="form-control form-control-sm"
                                        type="number" min="1" value="{{ $product->qty ??  0 }}">
                                </td>
                                <td>
                                    {{ $product->cal }}
                                </td>
                               
                                <td>
                                    {{ number_format($product->price) }}đ
                                </td>
                                <td>
                                    {{ number_format($product->price *$product->qty ??  0) }}đ
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>