<?php

namespace Botble\Warehouse\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Table\Columns\Column;
use Botble\Warehouse\Models\ActualOut;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\Warehouse\Enums\MaterialProposalStatusEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class ActualOutTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(ActualOut::class)
            ->addActions([
                EditAction::make()
                    ->route('actualout.material-out-confirm.edit'),
                DeleteAction::make()
                    ->route('actualout.material-out-confirm.destroy'),
            ])  ->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (ActualOut $item) {
                if (!$this->hasPermission('actualout.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('actualout.material-out-confirm.edit', $item->getKey()), BaseHelper::clean($item->name));
            }) ->editColumn('operator', function (ActualOut $item) {
                $actionPurchase = '';
                if ($item->status == MaterialProposalStatusEnum::PENDING && $this->hasPermission('material-receipt-confirm.confirm')) {
                    $actionPurchase = '
                    <a data-bs-toggle="tooltip" data-bs-original-title="Receipt" href="' . route('actualout.material-out-confirm.out-goods', $item->id) . '" class="btn btn-sm btn-icon btn-success"><i class="fa-solid fa-file-import"></i><span class="sr-only">Receipt</span></a>
                    ';
                }

                $actionView = '
                <a data-bs-toggle="tooltip" data-bs-original-title="View" href="' . route('material-receipt-confirm.view', $item->id) . '" class="btn btn-sm btn-icon btn-secondary"><i class="fa-regular fa-eye"></i><span class="sr-only">View</span></a>
                ';

                return '
                <div class="table-actions">
                    ' . $actionPurchase . '
                    ' . $actionView . '
                 </div>
                ';
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
                'confirm_out_id',
                'invoice_issuer_name',
                'invoice_confirm_name',
                'quantity',
                'general_order_code',
                'total_amount',
                'date_confirm',
                'created_at',
                'status',
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            Column::make('general_order_code')->title('Mã code chung'),
            Column::make('invoice_issuer_name')->title('Người chấp nhận xuất')
                ->width(150)
                ->orderable(false)
                ->searchable(false),

            Column::make('invoice_confirm_name')->title('Người thực xuất')
                ->width(150)
                ->orderable(false)
                ->searchable(false),
            Column::make('quantity')
                ->title('Tổng số lượng')
                ->searchable(false)
                ->orderable(false),
            StatusColumn::make(),
            CreatedAtColumn::make(),
            Column::make('operator')
            ->width(50)
            ->searchable(false)
            ->orderable(false),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('actualout.material-out-confirm.create'), 'actualout.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('actualout.material-out-confirm.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            'name' => [
                'title' => trans('core/base::tables.name'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => BaseStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', BaseStatusEnum::values()),
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'date',
            ],
        ];
    }

    public function getFilters(): array
    {
        return $this->getBulkChanges();
    }
}
