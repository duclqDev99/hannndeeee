@php
    use Botble\Base\Facades\BaseHelper;
    use Botble\WarehouseFinishedProducts\Enums\ProductIssueStatusEnum;

    $actionConfirm = '';
    if (($item->status == ProductIssueStatusEnum::PENDING || $item->status == ProductIssueStatusEnum::PENDINGISSUE) && (Auth::user()->hasPermission('hub-issue.confirm') || Auth::user()->hasPermission('hub-warehouse.all-permissions'))) {
        $actionConfirm =
            ' <a data-bs-toggle="tooltip" data-bs-original-title="Receipt" href="' .
            route('hub-issue.view-confirm', $item->id) .
            '" class="btn btn-sm btn-icon btn-success"><i class="fa-solid fa-file-import"></i><span class="sr-only">Receipt</span></a>
                    ';
    }
    $actionView = '';
    if ($actionConfirm == '') {
        $actionView =
            '
                    <a data-bs-toggle="tooltip" data-bs-original-title="View" href="' .
            route('hub-issue.view', $item->id) .
            '" class="btn btn-sm btn-icon btn-secondary"><i class="fa-regular fa-eye"></i><span class="sr-only">View</span></a>
                    ';
    }

@endphp

<div class="table-actions">
    {!! $actionConfirm !!}
    {!! $actionView !!}
</div>
