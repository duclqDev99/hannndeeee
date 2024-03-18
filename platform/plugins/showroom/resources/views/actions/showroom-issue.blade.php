@php
    use Botble\Base\Facades\BaseHelper;
    use Botble\WarehouseFinishedProducts\Enums\ProductIssueStatusEnum;

    $actionConfirm = '';
    if ($item->status == ProductIssueStatusEnum::PENDING && (Auth::user()->hasPermission('showroom-issue.confirm') || Auth::user()->hasPermission('showroom.all'))) {
        $actionConfirm =
            '
                    <a data-bs-toggle="tooltip" data-bs-original-title="Receipt" href="' .
            route('showroom-issue.confirmView', $item) .
            '" class="btn btn-sm btn-icon btn-success"><i class="fa-solid fa-file-import"></i><span class="sr-only">Receipt</span></a>
                    ';
    }
    $actionView = '';
    if ($actionConfirm == '' || $item->status != ProductIssueStatusEnum::PENDING) {
        $actionView =
            '
                    <a data-bs-toggle="tooltip" data-bs-original-title="View" href="' .
            route('showroom-issue.view', $item->id) .
            '" class="btn btn-sm btn-icon btn-secondary"><i class="fa-regular fa-eye"></i><span class="sr-only">View</span></a>
                    ';
    }

@endphp

<div class="table-actions">
    {!! $actionConfirm !!}
    {!! $actionView !!}
</div>
