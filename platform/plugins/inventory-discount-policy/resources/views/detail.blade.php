@php
    use Botble\SaleWarehouse\Models\SaleWarehouse;
    use Botble\Showroom\Models\Showroom;
@endphp


<div class="discount">
    <div class="discount-inner">
        <p class="discount-code">
            <span class="text-uppercase">Tên chính sách</span>:
            <b>{{ $item->name }}</b>
        </p>
        <p>
            <strong>
                {{ $item->type_warehouse == SaleWarehouse::class ? 'Dành cho kho sale' : 'Dành cho kho showroom' }}
            </strong>
        </p>
        <p class="discount-desc">
            Giảm giá <strong> {{ $item->value }} {{ $item->type_option == 'amount' ? 'VNĐ' : '%' }}</strong>
        </p>
    </div>
</div>
