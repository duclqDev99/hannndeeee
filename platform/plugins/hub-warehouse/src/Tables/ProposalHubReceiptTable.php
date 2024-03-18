<?php

namespace Botble\HubWarehouse\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\HubWarehouse\Models\HubUser;
use Botble\HubWarehouse\Models\ProposalHubReceipt;
use Botble\HubWarehouse\Models\Warehouse;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProposalHubReceiptTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(ProposalHubReceipt::class)
            ->addActions([
                EditAction::make()
                    ->route('proposal-hub-receipt.edit'),
                DeleteAction::make()
                    ->route('proposal-hub-receipt.destroy'),
            ])->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('warehouse_type', function (ProposalHubReceipt $item) {
                if ($item->warehouse_type) {
                    if ($item->warehouse_type == Warehouse::class && $item->warehouse_id == $item->warehouse_receipt_id) {
                        return 'Nhập lẻ';
                    }

                    return $item->warehouse
                        ? ($item->warehouse?->hub
                            ? $item->warehouse->name . ' - ' . $item->warehouse->hub->name
                            : ($item->warehouse?->agent
                                ? $item->warehouse->name . ' - ' . $item->warehouse->agent->name
                                : ($item->warehouse?->showroom
                                    ? $item->warehouse->name . ' - ' . $item->warehouse->showroom->name
                                    : 'Thành phẩm: ' . $item->warehouse->name
                                )
                            )
                        )
                        : '-----';
                } else {
                    return '-----';
                }
            })

            ->editColumn('general_order_code', function (ProposalHubReceipt $item) {
                return $item->general_order_code ?: '—';
            })
            ->editColumn('warehouse_name', function (ProposalHubReceipt $item) {

                return  $item->warehouseReceipt->name . ' - ' . $item->warehouseReceipt->hub->name;
            })
            ->editColumn('name', function (ProposalHubReceipt $item) {
                if (!$this->hasPermission('proposal-hub-receipt.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('proposal-hub-receipt.edit', $item->getKey()), BaseHelper::clean($item->name));
            })
            ->editColumn('proposal_code', function (ProposalHubReceipt $item) {
                return BaseHelper::clean(get_proposal_receipt_product_code($item->proposal_code));
            })
            ->editColumn('operator', function (ProposalHubReceipt $item) {

                return view('plugins/hub-warehouse::actions.hub-proposal-receipt', compact('item'))->render();
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $current_list_hub_id = get_list_hub_id_for_current_user();//Get id showroom of current user

        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'title',
                'invoice_issuer_name',
                'invoice_confirm_name',
                'warehouse_type',
                'warehouse_receipt_id',
                'warehouse_id',
                'proposal_code',
                'warehouse_name',
                'quantity',
                'issuer_id',
                'expected_date',
                'date_confirm',
                'status',
                'created_at',
                'general_order_code',
                'updated_at'
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
            ")->orderBy('expected_date', 'asc');

            if(\Auth::user()->isSuperUser()){
                return $this->applyScopes($query);
            }
            return $this->applyScopes($query->whereIn('warehouse_receipt_id', $current_list_hub_id));
    }

    public function columns(): array
    {
        return [
            Column::make('general_order_code')
                ->title('Mã đơn hàng')
                ->orderable(false),
            Column::make('proposal_code')
                ->title('Mã phiếu')
                ->orderable(false),
            Column::make('warehouse_name')
                ->title('Tên kho nhập'),
            Column::make('warehouse_type')
                ->title('Nhập từ'),
            Column::make('title')
                ->title('Mục đích')
                ->orderable(false),
            Column::make('quantity')
                ->title('Số lượng')
                ->orderable(false),
            Column::make('invoice_issuer_name')->title('Người đề xuất')
                ->orderable(false),
            CreatedAtColumn::make('expected_date')
                ->dateFormat('d/m/y')
                ->title('Ngày dự kiến')
                ->orderable(false),
            CreatedAtColumn::make('date_confirm')
                ->dateFormat('d/m/y')
                ->title('Ngày duyệt')
                ->orderable(false),
            StatusColumn::make('status')
                ->title('Trạng thái')
                ->width(50)
                ->orderable(false),
            Column::make('operator')
                ->width(100)->title('Hành động')
                ->orderable(false)
                ->searchable(false),
        ];
    }

    public function buttons(): array
    {
        if ($this->hasPermission('proposal-hub-receipt.create') || $this->hasPermission('hub-warehouse.all-permissions')) {
            return $this->addCreateButton(route('proposal-hub-receipt.create'));
        }
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
        return $this->getBulkChanges();
    }
    public function getCheckboxColumnHeading(): array
    {
        return [];
    }
}
