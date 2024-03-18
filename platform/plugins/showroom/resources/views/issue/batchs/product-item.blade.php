@php
    $totalQuantity = 0;
    $quantityStock = 0;

@endphp

<tr class="item__product">
    <td class="width-60-px min-width-60-px vertical-align-t" width="10%" style="margin:20px">
        <input type="hidden" id="product_id" name="product[{{ $orderProduct->id }}][product_id]"
            value="{{ $orderProduct->product_id }}" />
        <input type="hidden" id="warehouse_id" value="{{ $productIssue->warehouse_id }}" />
        <input type="hidden" id="orderProductId" value="{{ $orderProduct->id }}" />
        <div class="wrap-img">
            <img class="thumb-image thumb-image-cartorderlist" width="100px" height="100px"
                src="{{ RvMedia::getImageUrl($orderProduct->product($orderProduct->product_id)->first()?->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                alt="{{ $orderProduct->product($orderProduct->product_id)->first()?->name }}">
        </div>
    </td>
    <td class="pl5 p-r5 text-center " width = "30%">
        {{ $orderProduct->product_name }}
        @php
            echo ' <br> (Màu: ' . $orderProduct->color . '- Size: ' . $orderProduct->size . ')';
        @endphp
        <input type="text" class="widget__price" name="product[{{ $orderProduct->id }}][attr]"
            value="{{ $orderProduct->attribute }}" hidden="">
    </td>
    <td class="pl5 text-center" width="25%">
        <div class="inline_block">
            <input type="text" class="widget__batch" name="product[{{ $orderProduct->id }}][sku]"
                value="{{ $orderProduct->sku }}" hidden>
            <span>Mã: {{ $orderProduct->sku }}</span>
        </div>
    </td>

    <td class="pl5 p-r5" width="20%">
        <span style="white-space: nowrap;">Tồn kho:
            {{ $quantity }}</span>
    </td>
    <td class="pl5 p-r5  text-end" width="25%">
        <span style="white-space: nowrap;">Đề xuất: {{ $orderProduct->quantity }}</span>
    </td>
    </td>
</tr>
