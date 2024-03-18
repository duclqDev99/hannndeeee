<?php

namespace Botble\WarehouseFinishedProducts\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Table\Columns\Column;
use Botble\Warehouse\Enums\MaterialProposalStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProposalIssueStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;
use Botble\WarehouseFinishedProducts\Models\ProposalProductIssue;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\WarehouseFinishedProducts\Models\WarehouseUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProposalProductIssueTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(ProposalProductIssue::class)
            ->addActions([
                EditAction::make()
                    ->route('proposal-product-issue.edit'),
                DeleteAction::make()
                    ->route('proposal-product-issue.destroy'),
            ])
            ->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (ProposalProductIssue $item) {
                if (!$this->hasPermission('proposal-product-issue.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('proposal-product-issue.edit', $item->getKey()), BaseHelper::clean($item->name));
            })
            ->editColumn('proposal_code', function (ProposalProductIssue $item) {
                if(empty($item->proposal_code)){
                    return '--------';
                }
                return BaseHelper::clean(get_proposal_issue_product_code($item->proposal_code));
            })
            ->editColumn('warehouse_type', function (ProposalProductIssue $item) {
                if ($item->warehouse_type) {
                    return $item->warehouse->hub?->name
                        ? $item->warehouse->hub->name . ' - ' . $item->warehouse->name
                        : $item->warehouse->name;
                } else {
                    return 'Xuất lẻ';
                }
            })
            ->editColumn('general_order_code', function (ProposalProductIssue $item) {
                if (!empty($item->general_order_code)) {
                    return $item->general_order_code;
                }
                return '--------';
            })

            ->editColumn('operator', function (ProposalProductIssue $item) {

                return view('plugins/warehouse-finished-products::actions.wfp-proposal-issue',compact('item'))->render();
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
                'warehouse_id',
                'warehouse_name',
                'warehouse_address',
                'issuer_id',
                'invoice_issuer_name',
                'invoice_confirm_name',
                'warehouse_receipt_id',
                'warehouse_type',
                'general_order_code',
                'is_warehouse',
                'quantity',
                'title',
                'description',
                'proposal_code',
                'expected_date',
                'date_confirm',
                'created_at',
                'status',
            ])->orderByDesc(DB::raw('POSITION("pending" IN status)'))->orderByDesc(DB::raw('POSITION("examine" IN status)'))->orderByDesc(DB::raw('POSITION("denied" IN status)'))->orderByDesc('updated_at');
        if (!$this->hasPermission('warehouse-finished-products.warehouse-all')) {
            if ($this->hasPermission('proposal-product-issue.approve') || $this->hasPermission('proposal-product-issue.examine')) {
                $warehouseUsers = WarehouseUser::where('user_id', \Auth::id())->get();
                $warehouseIds = $warehouseUsers->pluck('warehouse_id')->toArray();
                $query->whereIn('warehouse_id', $warehouseIds);
            } else {
                $warehouseUsers = WarehouseUser::where('user_id', \Auth::id())->get();
                $warehouseIds = $warehouseUsers->pluck('warehouse_id')->toArray();
                $query->whereIn('warehouse_id', $warehouseIds)->where('issuer_id', Auth::user()->id);
            }
        }

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            Column::make('general_order_code')
                ->title('Mã đơn hàng')
                ->orderable(false)->width(50),
            Column::make('proposal_code')
                ->title('Mã phiếu')
                ->orderable(false),
            Column::make('warehouse_name')
                ->title('Kho xuất'),
            Column::make('warehouse_type')
                ->title('Xuất đến'),
            Column::make('title')
                ->title('Tên phiếu')
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
        return $this->addCreateButton(route('proposal-product-issue.create'), 'proposal-product-issue.create');
    }
    public function getCheckboxColumnHeading(): array
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
        return [
            'warehouse_name' => [
                'title' => 'Kho xuất',
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
