<?php

namespace Botble\ProcedureOrder\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\ProcedureOrder\Models\ProcedureOrder;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

use function Termwind\style;

class ProcedureOrderTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(ProcedureOrder::class)
            ->addActions([
                EditAction::make()
                    ->route('procedure-order.edit'),
                DeleteAction::make()
                    ->route('procedure-order.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (ProcedureOrder $item) {
                if (! $this->hasPermission('procedure-order.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('procedure-order.edit', $item->getKey()), BaseHelper::clean($item->name));
            })
            ->editColumn('main_thread_status', function (ProcedureOrder $item){
                switch ($item['main_thread_status']) {
                        case 'main_branch':
                        return Html::tag('span', 'Nhánh chính', ['class' => ' badge bg-success text-success-fg'])->toHtml();
                        break;
                    default:
                        return Html::tag('span', 'Nhánh phụ', ['class' => 'badge status-label', 'style' => 'color: #F5E8C7; background-color: #435585'])->toHtml();
                        break;
                }
            })
            ->editColumn('cycle_point', function (ProcedureOrder $item){
                switch ($item['cycle_point']) {
                    case 'start':
                        return Html::tag('span', 'nhánh bắt đầu', ['class' => 'badge status-label', 'style' => 'color: #040D12; background-color: #FFC93C'])->toHtml();
                    break;
                    case 'end':
                        return Html::tag('span', 'Nhánh kết thúc', ['class' => 'badge status-label', 'style' => 'color: #F5E8C7; background-color: #002B5B'])->toHtml();
                    break;
                    default:
                        break;
                }
            })
            // ->editColumn('parent_id', function (ProcedureOrder $item) {
            //     dd(ProcedureOrder::where('id', $item->id)->with('parent')->get());
            //     ProcedureOrder::with('parent')->get();
            //     if ($item) {
            //         return ProcedureOrder::where('id', $item->id)->with('parent')->get();
            //     } else {
            //         return null;
            //     }
            // })
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
               'name',
               'code',
               'cycle_point',
               'parent_id',
               'main_thread_status',
               'created_by',
               'created_at',
           ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            NameColumn::make(),
            Column::make('code')->title(trans('code')),
            Column::make('parent_id')->title(trans('parent')),
            Column::make('cycle_point')->title(trans('Cycle point')),
            Column::make('main_thread_status')->title(trans('status')),
            CreatedAtColumn::make(),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('procedure-order.create'), 'procedure-order.create');
    }

    public function bulkActions(): array
    {
        return [
            // DeleteBulkAction::make()->permission('procedure-order.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            // 'name' => [
            //     'title' => trans('core/base::tables.name'),
            //     'type' => 'text',
            //     'validate' => 'required|max:120',
            // ],
            // 'code' => [
            //     'title' => trans('code'),
            //     'type' => 'text',
            //     'validate' => 'required|max:255',
            // ],
            // 'main_thread_status' => [
            //     'title' => trans('core/base::tables.status'),
            //     'type' => 'select',
            //     'choices' => [
            //         'main_branch' => 'Nhánh chính',
            //         'secondary_branch' => 'Nhánh phụ',
            //     ],
            // ],
            // 'created_at' => [
            //     'title' => trans('core/base::tables.created_at'),
            //     'type' => 'date',
            // ],
        ];
    }

    public function getFilters(): array
    {
        return $this->getBulkChanges();
    }
}
