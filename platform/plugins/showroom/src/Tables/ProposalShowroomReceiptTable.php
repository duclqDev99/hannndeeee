<?php

namespace Botble\Showroom\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Showroom\Models\ShowroomProposalReceipt;
use Botble\Showroom\Models\ShowroomUser;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class ProposalShowroomReceiptTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(ShowroomProposalReceipt::class)
            ->addActions([
                EditAction::make()
                    ->route('proposal-showroom-receipt.edit'),
                DeleteAction::make()
                    ->route('proposal-showroom-receipt.destroy'),

            ])->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('warehouse_name', function (ShowroomProposalReceipt $item) {
                return $item->warehouse_name . ' - ' . $item->warehouseReceipt->showroom->name;
            })
            ->editColumn('proposal_code', function (ShowroomProposalReceipt $item) {
                return BaseHelper::clean(get_proposal_receipt_product_code($item->proposal_code));
            })
            ->editColumn('general_order_code', function (ShowroomProposalReceipt $item) {
                return $item->general_order_code ?: '—';
            })->editColumn('operator', function (ShowroomProposalReceipt $item) {

                return view('plugins/showroom::actions.showroom-proposal-receipt', compact('item'))->render();


            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'warehouse_receipt_id',
                'proposal_code',
                'warehouse_name',
                'warehouse_address',
                'issuer_id',
                'invoice_issuer_name',
                'invoice_confirm_name',
                'general_order_code',
                'quantity',
                'title',
                'description',
                'expected_date',
                'date_confirm',
                'status',
            ])->orderByRaw(" CASE status
            WHEN 'pending' THEN 1
            WHEN 'wait' THEN 2
            WHEN 'approved' THEN 3
            WHEN 'confirm' THEN 4
            WHEN 'denied' THEN 5
            WHEN 'refuse' THEN 6
            ELSE 7
        END ASC
            ")->orderBy('expected_date', 'asc')
            ->when(!request()->user()->hasPermission('showroom.all'), function ($q) {
                $authUserId = request()->user()->id;
                $userHub = ShowroomUser::where('user_id', $authUserId)->pluck('showroom_id');
                if (request()->user()->hasPermission('proposal-showroom-receipt.approve')) {
                    return $q->whereHas('warehouseReceipt.showroom', function ($query) use ($userHub) {
                        $query->whereIn('id', $userHub);
                    });
                } else {
                    return $q->whereHas('warehouseReceipt.showroom', function ($query) use ($userHub) {
                        $query->whereIn('id', $userHub)->where('issuer_id', request()->user()->id);
                    });
                }
            });

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            Column::make('proposal_code')->title('Mã phiếu'),
            Column::make('general_order_code')->title('Mã đơn hàng'),
            Column::make('warehouse_name')->title('Tên kho nhận'),
            Column::make('title')->title('Tiêu đề'),
            Column::make('invoice_issuer_name')->title('Người đề xuất'),
            Column::make('quantity')->title('Số lượng đề xuất'),

            CreatedAtColumn::make('expected_date')->title('Ngày đề xuất')->dateFormat('d-m-y'),
            CreatedAtColumn::make('date_confirm')->title('Ngày nhận')->dateFormat('d-m-y'),
            StatusColumn::make(),
            Column::make('operator')
                ->width(100)->title('Hành động')
                ->orderable(false)
                ->searchable(false),
        ];
    }

    public function getCheckboxColumnHeading(): array
    {
        return [];
    }

    public function buttons(): array
    {
        if ($this->hasPermission('proposal-showroom-receipt.create') || $this->hasPermission('showroom.all')) {
            return $this->addCreateButton(route('proposal-showroom-receipt.create'));
        }
        return [];
    }

    public function bulkActions(): array
    {
        return [
        ];
    }

    public function getBulkChanges(): array
    {
        return [

        ];
    }

    public function getFilters(): array
    {
        return $this->getBulkChanges();
    }

}
