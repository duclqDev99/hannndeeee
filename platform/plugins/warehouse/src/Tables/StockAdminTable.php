<?php

namespace Botble\Warehouse\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Warehouse\Models\Stock;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\Table\Columns\Column;
use Botble\Base\Facades\Assets;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;

class StockAdminTable extends TableAbstract
{
    public function setup(): void
    {
        Assets::addScriptsDirectly(
            [
                'https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js',
                'https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js',
            ]
        );
        $this
            ->model(Stock::class)
            ->addActions([
                EditAction::make()
                    ->route('stock.edit'),
                DeleteAction::make()
                    ->route('stock.destroy'),
            ])
            ->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('branch_name', function (Stock $item) {
                return BaseHelper::clean($item->branch->name);
            })
            ->editColumn('count_product', function (Stock $item) {
                return BaseHelper::clean($item->countProduct($item->branch_id));
            })
            ->editColumn('quantity', function (Stock $item) {
                return BaseHelper::clean($item->totalQtyByBranch($item->branch_id));
            })
            ->editColumn('operator', function (Stock $item) {
                return '
                <a data-bs-toggle="tooltip" data-bs-original-title="Xem chi tiết" href="'.route('stock.detail', $item->branch_id).'" class="btn btn-sm btn-icon btn-success"><i class="fa-solid fa-file-import"></i><span class="sr-only">Xem chi tiết</span></a>
                ';
            });

        $data = $data
        ->filter(function ($query) {
            if ($keyword = $this->request->input('search.value')) {
                return $query
                    ->whereHas('branch', function ($subQuery) use ($keyword) {
                        return $subQuery
                            ->where('name', 'LIKE', '%' . $keyword . '%');
                    })
                    ->orWhere('proposal_code', 'LIKE', '%' . $keyword . '%');
            }

            return $query;
        });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'branch_id',
            ])->orderByDesc('branch_id')->groupBy('branch_id');

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            Column::make('branch_name')
                ->orderable(false),
            Column::make('count_product')
                ->title('Số sản phẩm trong kho')
                ->orderable(false)
                ->searchable(false),
            Column::make('quantity')
                ->title('Số lượng')
                ->searchable(false),
            Column::make('operator')
                ->title('Chức năng')
                ->searchable(false)
                ->orderable(false),
        ];
    }

    public function htmlDrawCallbackFunction(): string|null
    {
        return parent::htmlDrawCallbackFunction() . '$(".editable").editable({mode: "inline"});';
    }

    public function getBulkChanges(): array
    {
        return [
            'branch_name' => [
                'title' => __('Tên kho'),
                'type' => 'text',
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

    public function applyFilterCondition(
        Builder|QueryBuilder|Relation $query,
        string $key,
        string $operator,
        string|null $value
    ): Builder|QueryBuilder|Relation {
        switch ($key) {
            case 'branch_name':
                if (! $value) {
                    break;
                }

                return $this->filterByCustomer($query, 'name', $operator, $value);
        }

        return parent::applyFilterCondition($query, $key, $operator, $value);
    }

    protected function filterByCustomer(
        Builder|QueryBuilder|Relation $query,
        string $column,
        string $operator,
        string|null $value
    ): Builder|QueryBuilder|Relation {
        if ($operator === 'like') {
            $value = '%' . $value . '%';
        } elseif ($operator !== '=') {
            $operator = '=';
        }

        return $query
            ->where(function ($query) use ($column, $operator, $value) {
                if(Schema::hasColumn('finished_branch', $column))
                {
                    $query
                    ->whereHas('branch', function ($subQuery) use ($column, $operator, $value) {
                        $subQuery->where($column, $operator, $value);
                    });
                }
            });
    }
}
