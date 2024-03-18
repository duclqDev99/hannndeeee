@php
    use Botble\Base\Facades\BaseHelper;
    use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;


    $actionApprove = '';
    $actionDelete = '';

    if ($item->status == ProposalProductEnum::PENDING) {
        if (Auth::user()->hasPermission('proposal-receipt-products.censorship')) {
            $actionApprove =
            '
                        <li><a data-bs-original-title="Xác nhận đơn" href="' .
                route('proposal-receipt-products.censorship', $item->id) .
                '" class="dropdown-item">Xác nhận đơn</a></li>
                        ';
        }
        if (!isset($item->proposal_issue_id)) {
            $actionDelete =
                '
                        <li><a data-bs-original-title="Xoá" href="' .
                route('proposal-receipt-products.destroy', $item->id) .
                '" class="dropdown-item" data-dt-single-action="" data-method="DELETE" data-confirmation-modal="true" data-confirmation-modal-title="Xác nhận xoá" data-confirmation-modal-message="Bạn có thực sự muốn xoá đối tượng này?" data-confirmation-modal-button="Xoá" data-confirmation-modal-cancel-button="Thoát">
                            Xoá
                        </a></li>
                        ';
        }
    }

    $actionView = '';
    if ($item->status != ProposalProductEnum::PENDING || !Auth::user()->hasPermission('proposal-receipt-products.index')) {
        $actionView =
            '
                    <li><a data-bs-original-title="View" href="' .
            route('proposal-receipt-products.view', $item) .
            '" class="dropdown-item">Xem chi tiết</a></li>
                    ';
    }

    $actionEdit = '';
    if ($item->status != ProposalProductEnum::APPOROVED && $item->status != ProposalProductEnum::CONFIRM && $item->status != ProposalProductEnum::WAIT && Auth::user()->hasPermission('proposal-receipt-products.edit')) {
        $actionEdit =
            '
                    <li><a data-bs-original-title="Edit" href="' .
            route('proposal-receipt-products.edit', $item->id) .
            '" class="dropdown-item">Chỉnh sửa</a></li>
                    ';
    }
@endphp

<div class="table-actions">
    <div class="dropdown">
        <button class="btn dropdown-toggle" type="button" id="dropdown-actions-bfc2f2437bffdcf1ed1dc1928f640f8a-1"
            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Hành động
        </button>
        <ul class="dropdown-menu">
            {!! $actionApprove !!}
            {!! $actionView !!}
            {!! $actionEdit !!}
            {!! $actionDelete !!}
        </ul>
    </div>
</div>
<style>
    .card-table .dropdown,
    .card-table .btn-group-vertical {
        position: static;
    }
</style>
