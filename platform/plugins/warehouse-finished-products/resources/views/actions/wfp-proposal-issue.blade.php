@php
    use Botble\Base\Facades\BaseHelper;
    use Botble\WarehouseFinishedProducts\Enums\ProposalIssueStatusEnum;
    $actionExamine = '';
    $actionApprove = '';
    $actionDelete = '';
    if ($item->status == ProposalIssueStatusEnum::PENDING || $item->status == ProposalIssueStatusEnum::EXAMINE) {
        if ($item->issuer_id == Auth::user()->id && $item->status == ProposalIssueStatusEnum::PENDING) {
            $actionDelete =
                ' <li><a data-bs-toggle="tooltip" data-bs-original-title="Delete"   class="dropdown-item text-danger"
                        href="' .
                route('proposal-product-issue.destroy', $item->id) .
                '"
                         data-dt-single-action="" data-method="DELETE" data-confirmation-modal="true" data-confirmation-modal-title="Xóa đơn hàng"
                          data-confirmation-modal-message="Khi xóa không thể khôi phục lại đơn hàng?" data-confirmation-modal-button="Xóa đơn"
                          data-confirmation-modal-cancel-button="Đóng">

                          ' .
                BaseHelper::renderIcon('ti ti-trash') .
                '

                      <span class="ms-1">    Xóa đơn đề xuất</span>  </a></li>';
        }
        if (Auth::user()->hasPermission('proposal-product-issue.examine') && $item->status == ProposalIssueStatusEnum::PENDING) {
            $actionExamine =
                '
                        <li> <a data-bs-toggle="tooltip" class="dropdown-item text-success" href="' .
                route('proposal-product-issue.approveProposalProductIssue', $item->id) .
                '" class="btn btn-sm btn-icon btn-success">
                ' .
                BaseHelper::renderIcon('ti ti-archive') .'Duyệt đơn</a></li>';
        }
        if (Auth::user()->hasPermission('proposal-product-issue.approve') && $item->status == ProposalIssueStatusEnum::EXAMINE) {
            $actionApprove =
                '
                        <li> <a data-bs-toggle="tooltip" class="dropdown-item text-success" href="' .
                route('proposal-product-issue.approveProposalProductIssue', $item->id) .
                '" class="btn btn-sm btn-icon btn-success">
                ' .
                BaseHelper::renderIcon('ti ti-archive') .
                'Xác nhận</a></li>                        ';
        }
    }
    if ($actionApprove == '' && $actionExamine == '') {
        $actionView =
            '
                    <li><a data-bs-toggle="tooltip" class="dropdown-item text-warning" data-bs-original-title="View"  class="dropdown-item"   href="' .
            route('proposal-product-issue.view', $item->id) .
            '" class="btn btn-sm btn-icon btn-secondary">    ' .
            BaseHelper::renderIcon('ti ti-eye') .
            'Xem chi tiết</a></li>
                    ';
    } else {
        $actionView = '';
    }
    $editView = '';
    if (($item->status->toValue() === ProposalIssueStatusEnum::PENDING || $item->status->toValue() === ProposalIssueStatusEnum::DENIED) && $item->issuer_id == Auth::user()->id) {
        $editView =
            '
                    <li><a data-bs-toggle="tooltip" class="dropdown-item text-primary" data-bs-original-title="Edit" href="' .
            route('proposal-product-issue.edit', $item->id) .
            '" class="btn btn-sm btn-icon btn-secondary"> <span class="icon-tabler-wrapper">
                ' .
            BaseHelper::renderIcon('ti ti-edit') .
            '
                  </span>Cập nhật đơn</a></li>
                    ';
    }
@endphp

<div class="table-actions">
    <div class="dropdown">
        <button class="btn dropdown-toggle" type="button" id="dropdown-actions-{{ $item->id }}"
            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Hành động
        </button>
        <ul class="dropdown-menu">
            {!! $actionExamine !!}
            {!! $actionApprove !!}
            {!! $actionView !!}
            {!! $editView !!}
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
