
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
        </tr>
    </thead>
    <tbody id="product-preview">
        @foreach ($products as $index => $product)
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
                        type="number" min="1" value="{{ $product->qty ?? 0 }}">
                </td>
                <td>
                    {{ $product->cal }}
                </td>
               
                <td>
                    {{ number_format($product->price) }}đ
                </td>
                <td>
                    {{ number_format($product->price * $product->qty ?? 0) }}đ
                </td>
                {{-- <td>
                    <a data-id="{{ $product->id }}" type="button" data-bs-toggle="modal"
                        data-bs-target="#edit-product-modal" class="edit-product-btn">Xem chi
                        tiết</a>
                </td> --}}
            </tr>
        @endforeach
    </tbody>
</table>