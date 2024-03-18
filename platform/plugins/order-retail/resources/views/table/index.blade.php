@extends($layout ?? BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @include('core/table::base-table')
    @include('plugins/order-retail::modal.status-detail')
    @include('plugins/order-retail::modal.upload-contract')

    <x-core::modal
    :id="'confirm-update-action-modal'"
    :title="'Ghi chú nếu có'"
    :size="'asd'"
    :button-id="'confirm-update-action-btn'"
    :button-label="'Xác nhận'"
>
    <form id="form-confirm-action">
        <div class="mb-3">
            <label for="exampleFormControlTextarea1" class="form-label">Ghi chú</label>
            <textarea class="form-control" id="exampleFormControlTextarea1" name="note" rows="3"></textarea>
        </div>
    </form>
</x-core::modal>

    @push('footer')
        <script>
            
        </script>
    @endpush
@endsection
