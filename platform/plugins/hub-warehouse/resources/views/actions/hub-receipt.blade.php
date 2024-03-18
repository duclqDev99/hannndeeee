@php
    use Botble\WarehouseFinishedProducts\Enums\ApprovedStatusEnum;
    $actionConfirm = '';
    if ($item->status == ApprovedStatusEnum::PENDING) {
        if (Auth::user()->hasPermission('hub-receipt.confirm') || Auth::user()->hasPermission('hub-warehouse.all-permissions')) {
            $actionConfirm =
                '<a data-bs-toggle="tooltip" data-bs-original-title="Receipt" href="' .
                route('hub-receipt.confirm', $item->id) .
                '"
                        class="btn btn-sm btn-icon btn-success">
                        <i class="fa-solid fa-file-import"></i><span class="sr-only">
                        Receipt</span></a>';
        }
    }
    $actionView = '';
    // if ($actionConfirm == '') {
    if ($item->status != ApprovedStatusEnum::PENDING && $actionConfirm == '') {
        $actionView =
            '
                        <a data-bs-toggle="tooltip" data-bs-original-title="View" href="' .
            route('hub-receipt.view', $item->id) .
            '"
                        class="btn btn-sm btn-icon btn-secondary">
                        <i class="fa-regular fa-eye"></i><span class="sr-only">
                        View</span></a>
                        ';
    }

@endphp


<div class="table-actions d-flex" style="gap: 5px;">
    {!! $actionConfirm !!}
    {!! $actionView !!}
</div>
