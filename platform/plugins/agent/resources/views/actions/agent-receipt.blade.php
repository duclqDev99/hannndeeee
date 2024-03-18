@php
    use Botble\WarehouseFinishedProducts\Enums\ApprovedStatusEnum;
    $actionPurchase = '';
    if ($item->status == ApprovedStatusEnum::PENDING) {
        if (Auth::user()->hasPermission('agent-receipt.confirm') || Auth::user()->hasPermission('agent.all')) {
            $actionPurchase =
                '
        <a data-bs-toggle="tooltip" data-bs-original-title="Xác nhận đơn" href="' .
                route('agent-receipt.confirmView', $item->id) .
                '" class="btn btn-sm btn-icon btn-success"><i class="fa-solid fa-file-import"></i><span class="sr-only">Xác nhận đơn</span></a>
        ';
        }
    }

    $actionView = '';
    if ($actionPurchase == '') {
        $actionView =
            '
    <a data-bs-toggle="tooltip" data-bs-original-title="View" href="' .
            route('agent-receipt.view', $item) .
            '" class="btn btn-sm btn-icon btn-secondary"><i class="fa-regular fa-eye"></i><span class="sr-only">View</span></a>
    ';
    }

@endphp


<div class="table-actions d-flex" style="gap: 5px;">
    {!! $actionPurchase !!}
    {!! $actionView !!}
</div>
