<?php

namespace Botble\WarehouseFinishedProducts\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\WarehouseFinishedProducts\Enums\ApprovedStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProductIssueStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\Columns\StatusColumn;
use Botble\WarehouseFinishedProducts\Models\ProductIssue;
use Botble\WarehouseFinishedProducts\Models\WarehouseUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProductIssueTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(ProductIssue::class)
            ->addActions([
                EditAction::make()
                    ->route('product-issue.edit'),
                DeleteAction::make()
                    ->route('product-issue.destroy'),
            ])->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (ProductIssue $item) {
                if (!$this->hasPermission('product-issue.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('product-issue.edit', $item->getKey()), BaseHelper::clean($item->name));
            })
            ->editColumn('proposal_code', function (ProductIssue $item) {
                return $item->proposal?->proposal_code;
            })
            ->editColumn('warehouse_receipt_id', function (ProductIssue $item) {
                if ($item->warehouse_type) {
                    return $item->warehouse->hub?->name
                        ? $item->warehouse->name . ' - ' . $item->warehouse->hub->name
                        : $item->warehouse->name;
                } else {
                    return '-----';
                }
            })
            ->editColumn('issue_code',function(ProductIssue $item){
                return BaseHelper::clean(get_proposal_issue_product_code($item->issue_code));
            })
            ->editColumn('operator', function (ProductIssue $item) {
                return view('plugins/warehouse-finished-products::actions.wfp-issue',compact('item'))->render();
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
                'warehouse_id',
                'warehouse_name',
                'warehouse_address',
                'issuer_id',
                'invoice_issuer_name',
                'invoice_confirm_name',
                'warehouse_receipt_id',
                'warehouse_type',
                'general_order_code',
                'quantity',
                'title',
                'description',
                'expected_date',
                'date_confirm',
                'created_at',
                'status',
                'issue_code'
            ])->orderByDesc(DB::raw('POSITION("pending" IN status)'))->orderByDesc('created_at');

        if (!$this->hasPermission('warehouse-finished-products.warehouse-all')) {
            $warehouseUsers = WarehouseUser::where('user_id', \Auth::id())->get();
            $warehouseIds = $warehouseUsers->pluck('warehouse_id')->toArray();
            $query->whereIn('warehouse_id', $warehouseIds);
        }
        return $this->applyScopes($query);

    }

    public function columns(): array
    {
        return [
            Column::make('issue_code')->title('Mã phiếu'),
            Column::make('warehouse_name')->title('Kho xuât'),
            Column::make('warehouse_receipt_id')->title('Nơi đến'),
            Column::make('title')->title('Tiêu đề'),
            CreatedAtColumn::make('expected_date')->dateFormat('d/m/y')->title('Ngày dự kiến'),
            CreatedAtColumn::make('date_confirm')->dateFormat('d/m/y')->title('Ngày xuất kho'),
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
    public function getCheckboxColumnHeading(): array
    {
        return [];
    }

    public function bulkActions(): array
    {
        return [
            // DeleteBulkAction::make()->permission('product-issue.destroy'),
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
                'title' => 'Kho thành phẩm',
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'title' => [
                'title' => 'Tiêu đề',
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'expected_date' => [
                'title' => 'Ngày dự kiến',
                'type' => 'date',
            ],
            'date_confirm' => [
                'title' => 'Ngày xuất kho',
                'type' => 'date',
            ],
            'created_at' => [
                'title' => 'Ngày tạo',
                'type' => 'date',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => ProductIssueStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', ProductIssueStatusEnum::values()),
            ],
        ];
    }


}
