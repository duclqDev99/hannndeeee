@php
    use Botble\WarehouseFinishedProducts\Enums\ApprovedStatusEnum;
    $actionConfirm = '';
    if ($item->status == ApprovedStatusEnum::PENDING) {
        if (Auth::user()->hasPermission('sale-receipt.confirm')) {
            $actionConfirm =
                '<a data-bs-toggle="tooltip" data-bs-original-title="Xác nhận đơn" href="' .
                route('sale-receipt.confirmView', $item->id) .
                '" class="btn btn-sm btn-icon btn-success"><i class="fa-solid fa-file-import"></i><span class="sr-only">Xác nhận đơn</span></a>
                        ';
        }
    }

    $actionView = '';
    if ($item->status != ApprovedStatusEnum::PENDING || $actionConfirm == '') {
        $actionView =
            '<a data-bs-toggle="tooltip" data-bs-original-title="View" href="' .
            route('sale-receipt.view', $item->id) .
            '" class="btn btn-sm btn-icon btn-secondary"><i class="fa-regular fa-eye"></i><span class="sr-only">View</span></a>
                    ';
    }
@endphp


<div class="table-actions d-flex" style="gap: 5px;">
    {!! $actionConfirm !!}
    {!! $actionView !!}
</div>
