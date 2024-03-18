<?php

namespace Botble\WarehouseFinishedProducts\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\WarehouseFinishedProducts\Models\ProposalGoodReceipts;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\WarehouseFinishedProducts\Actions\AcceptAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;
use Botble\WarehouseFinishedProducts\Models\ProposalReceiptProducts;
use Botble\WarehouseFinishedProducts\Models\WarehouseUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProposalReceiptProductTable extends TableAbstract
{


    public function setup(): void
    {
        $this
            ->model(ProposalReceiptProducts::class)
            ->addActions([
                EditAction::make()
                    ->route('proposal-receipt-products.edit'),
                DeleteAction::make()
                    ->route('proposal-receipt-products.destroy'),
            ])
            ->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())

            ->editColumn('name', function (ProposalReceiptProducts $item) {
                if (!$this->hasPermission('proposal-receipt-products.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('proposal-receipt-products.edit', $item->getKey()), BaseHelper::clean($item->name));
            })
            ->editColumn('proposal_code', function(ProposalReceiptProducts $item){
                if(empty($item->proposal_code)){
                    return '--------';
                }
                return BaseHelper::clean(get_proposal_receipt_product_code($item->proposal_code));
            })
            ->editColumn('date_confirm', function (ProposalReceiptProducts $item) {
                if (empty($item->date_confirm)) {
                    return '--------';
                }
                return $item->date_confirm;
            })
            ->editColumn('status', function (ProposalReceiptProducts $item) {

                return $item->status->toHtml();
            })
            ->editColumn('general_order_code', function (ProposalReceiptProducts $item) {
                if (!empty($item->general_order_code)) {
                    return $item->general_order_code;
                }
                return '--------';
            })
            ->editColumn('operators', function (ProposalReceiptProducts $item) {



                return  view('plugins/warehouse-finished-products::actions.wfp-proposal-receipt',compact('item'))->render();
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
                'general_order_code',
                'proposal_code',
                'warehouse_id',
                'warehouse_name',
                'warehouse_address',
                'isser_id',
                'invoice_issuer_name',
                'invoice_confirm_name',
                'wh_departure_id',
                'wh_departure_name',
                'is_warehouse',
                'quantity',
                'title',
                'description',
                'expected_date',
                'date_confirm',
                'reasoon_cancel',
                'proposal_issue_id',
                'status',
                'created_at',
            ])->orderByRaw("
            CASE status
                WHEN 'pending' THEN 1
                WHEN 'wait' THEN 2
                WHEN 'confirm' THEN 3
                WHEN 'denied' THEN 4
                WHEN 'refuse' THEN 5
                WHEN 'approved' THEN 6
                ELSE 7
            END ASC
        ")->orderBy('created_at', 'desc');
        if (!$this->hasPermission('warehouse-finished-products.warehouse-all')) {
            $warehouseUsers = WarehouseUser::where('user_id', \Auth::id())->get();
            $warehouseIds = $warehouseUsers->pluck('warehouse_id')->toArray();
            if ($this->hasPermission('proposal-receipt-products.censorship')) {
                $query->whereIn('warehouse_id', $warehouseIds);
            } else {
                $query->whereIn('warehouse_id', $warehouseIds)->where('isser_id', Auth::user()->id);
            }
        }

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            Column::make('general_order_code')
                ->title('Mã đơn hàng')
                ->width(120)
                ->orderable(false),
            Column::make('proposal_code')
                ->title('Mã phiếu')
                ->width(120)
                ->orderable(false),
            Column::make('title')
                ->width(350)
                ->title('Mục đích')
                ->orderable(false)
                ->searchable(false),
            Column::make('warehouse_name')
                ->title('Tên kho nhập')
                ->orderable(false)
                ->searchable(false),
            CreatedAtColumn::make()
                ->dateFormat('d/m/Y')
                ->title('Ngày tạo'),
            CreatedAtColumn::make('expected_date')
                ->dateFormat('d/m/Y')
                ->title('Ngày dự kiến')
                ->orderable(false)
                ->searchable(false),
            CreatedAtColumn::make('date_confirm')
                ->dateFormat('d/m/Y')
                ->title('Ngày duyệt')
                ->orderable(false)
                ->searchable(false),
            Column::make('status')
                ->width(50)
                ->title('Trạng thái'),
            Column::make('operators')
                ->width(50)
                ->title('Chức năng')
                ->orderable(false)
                ->searchable(false)
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('proposal-receipt-products.create'), 'proposal-receipt-products.create');
    }

    public function getCheckboxColumnHeading(): array
    {
        return [];
    }

    public function bulkActions(): array
    {
        return [];
    }

    public function getBulkChanges(): array
    {
        return [];
    }

    public function getFilters(): array
    {
        return [
            'warehouse_name' => [
                'title' => 'Kho thành phẩm',
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'title' => [
                'title' => 'Tiêu đề',
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'proposal_code' => [
                'title' => 'Mã đơn đề xuất',
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'expected_date' => [
                'title' => 'Ngày dự kiến',
                'type' => 'date',
            ],
            'date_confirm' => [
                'title' => 'Ngày duyệt',
                'type' => 'date',
            ],
            'created_at' => [
                'title' => 'Ngày tạo',
                'type' => 'date',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => ProposalProductEnum::labels(),
                'validate' => 'required|in:' . implode(',', ProposalProductEnum::values()),
            ],
        ];
    }
}
