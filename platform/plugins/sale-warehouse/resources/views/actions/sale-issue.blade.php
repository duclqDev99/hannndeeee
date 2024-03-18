@php
    use Botble\Base\Facades\BaseHelper;
    use Botble\WarehouseFinishedProducts\Enums\ProductIssueStatusEnum;

    $actionConfirm = '';
    if (($item->status == ProductIssueStatusEnum::PENDING || $item->status == ProductIssueStatusEnum::PENDINGISSUE) && (Auth::user()->hasPermission('sale-issue.confirm') || Auth::user()->hasPermission('sale-warehouse.all'))) {
        $actionConfirm =
            ' <a data-bs-toggle="tooltip" data-bs-original-title="Receipt" href="' .
            route('sale-issue.view-confirm', $item->id) .
            '" class="btn btn-sm btn-icon btn-success"><i class="fa-solid fa-file-import"></i><span class="sr-only">Receipt</span></a>
                    ';
    }
    $actionView = '';
    if ($actionConfirm == '') {
        $actionView =
            '
                    <a data-bs-toggle="tooltip" data-bs-original-title="View" href="' .
            route('sale-issue.view', $item->id) .
            '" class="btn btn-sm btn-icon btn-secondary"><i class="fa-regular fa-eye"></i><span class="sr-only">View</span></a>
                    ';
    }

@endphp

<div class="table-actions">
    {!! $actionConfirm !!}
    {!! $actionView !!}
</div>
