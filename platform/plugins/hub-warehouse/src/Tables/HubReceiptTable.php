<?php

namespace Botble\HubWarehouse\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\HubWarehouse\Models\HubReceipt;
use Botble\HubWarehouse\Models\HubUser;
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
use Botble\WarehouseFinishedProducts\Enums\ApprovedStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HubReceiptTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(HubReceipt::class)
            ->addActions([
                EditAction::make()
                    ->route('hub-receipt.edit'),
                DeleteAction::make()
                    ->route('hub-receipt.destroy'),
            ])->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (HubReceipt $item) {
                if (!$this->hasPermission('product-issue.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('product-issue.edit', $item->getKey()), BaseHelper::clean($item->name));
            })
            ->editColumn('warehouse_receipt_id', function (HubReceipt $item) {
                return $item->warehouseReceipt->name . ' - ' . $item->warehouseReceipt->hub->name;
            })
            ->editColumn('warehouse_type', function (HubReceipt $item) {
                if ($item->warehouse_type == Warehouse::class && $item->warehouse_id == $item->warehouse_receipt_id) {
                    return 'Nhập hàng tồn';
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

            })
            ->editColumn('receipt_code', function (HubReceipt $item) {
                return BaseHelper::clean(get_proposal_receipt_product_code($item->receipt_code));
            })
            ->editColumn('operator', function (HubReceipt $item) {
                return view('plugins/hub-warehouse::actions.hub-receipt',compact('item'))->render();
            })
        ;


        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'proposal_id',
                'warehouse_receipt_id',
                'warehouse_name',
                'warehouse_address',
                'issuer_id',
                'invoice_issuer_name',
                'invoice_confirm_name',
                'warehouse_id',
                'warehouse_type',
                'general_order_code',
                'quantity',
                'title',
                'description',
                'expected_date',
                'date_confirm',
                'created_at',
                'status',
                'receipt_code'

            ])->when(!request()->user()->hasPermission('hub-warehouse.all-permissions'), function ($q) {
                $authUserId = request()->user()->id;
                $userHub = HubUser::where('user_id', $authUserId)->pluck('hub_id'); // Assuming you have a 'hub_id' column in HubUser
                if (request()->user()->hasPermission('hub-receipt.confirm')) {
                    $q->whereHas('warehouseReceipt.hub', function ($query) use ($userHub) {
                        $query->whereIn('id', $userHub);
                    });
                } else {
                    $q->whereHas('warehouseReceipt.hub', function ($query) use ($userHub) {
                        $query->whereIn('id', $userHub)->where('issuer_id', request()->user()->id);
                        ;
                    });
                }

            })->orderByRaw("
            CASE status
                WHEN 'pending' THEN 1
                WHEN 'cancel' THEN 2
                WHEN 'approved' THEN 3
                ELSE 7
            END ASC
        ")->orderBy('expected_date', 'asc');
        ;

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [

            Column::make('receipt_code')->title('Mã phiếu'),
            Column::make('warehouse_receipt_id')->title('Kho nhập'),
            Column::make('warehouse_type')->title('Nhập từ'),
            Column::make('title')->title('Mục đích'),
            Column::make('quantity')->title('Số lượng'),
            CreatedAtColumn::make('expected_date')->dateFormat('d/m/y')->title('Ngày dự kiến'),
            CreatedAtColumn::make('date_confirm')->dateFormat('d/m/y')->title('Ngày nhập kho'),
            StatusColumn::make('status')->title('Trạng thái'),
            Column::make('operator')
                ->title('Hành động')
                ->orderable(false)
                ->searchable(false),
        ];
    }

    public function buttons(): array
    {
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
    public function getCheckboxColumnHeading(): array
    {
        return [];
    }


}
