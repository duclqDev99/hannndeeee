<div class="row">
    {{-- <div class="col-8">
        <x-core::form.text-input :required="true" :label="'Tên kho lấy hàng'" name="pick_name" class="input-sync-text-item"
            data-target=".label-rule-item-name" :value="''" />
    </div> --}}
    <div class="col">
        <x-core::form.text-input :required="true" :label="'Id kho lấy hàng'" name="pick_address_id"
            class="input-sync-text-item" data-target=".label-rule-item-name" :value="$pickAddress?->pick_address_id" />
    </div>
</div>

<div class="row">
    <div class="col-4">
        <x-core::form.text-input :required="true" :label="'ID Tỉnh/thành phố'" name="province_id" class="input-sync-text-item"
            data-target=".label-rule-item-name" :value="$pickAddress?->province_id" />
    </div>
    <div class="col-4">
        <x-core::form.text-input :required="true" :label="'ID Quận/huyện'" name="district_id"
            class="input-sync-text-item" data-target=".label-rule-item-name" :value="$pickAddress?->district_id" />
    </div>
    <div class="col-4">
        <x-core::form.text-input :required="true" :label="'ID Phường/xã'" name="district_id"
            class="input-sync-text-item" data-target=".label-rule-item-name" :value="$pickAddress?->ward_id" />
    </div>
</div>