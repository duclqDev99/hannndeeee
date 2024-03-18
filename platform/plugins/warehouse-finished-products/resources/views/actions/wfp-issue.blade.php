@php
    use Botble\Base\Facades\BaseHelper;
    use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;

    $actionConfirm = '';
    if ($item->status == ProposalProductEnum::PENDING && Auth::user()->hasPermission('product-issue.confirm')) {
        $actionConfirm =
            '
                    <a data-bs-toggle="tooltip" data-bs-original-title="Receipt" href="' .
            route('product-issue.view-confirm', $item->id) .
            '" class="btn btn-sm btn-icon btn-success"><i class="fa-solid fa-file-import"></i><span class="sr-only">Receipt</span></a>
                    ';
    }
    $actionView = '';
    if ($actionConfirm == '' || $item->status != ProposalProductEnum::PENDING) {
        $actionView =
            '
                    <a data-bs-toggle="tooltip" data-bs-original-title="View" href="' .
            route('product-issue.view', $item->id) .
            '" class="btn btn-sm btn-icon btn-secondary"><i class="fa-regular fa-eye"></i><span class="sr-only">View</span></a>
                    ';
    }

@endphp

<div class="table-actions">
    {!! $actionConfirm !!}
    {!! $actionView !!}
</div>
