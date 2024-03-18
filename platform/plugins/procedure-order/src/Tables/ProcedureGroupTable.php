<?php

namespace Botble\ProcedureOrder\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\ProcedureOrder\Models\ProcedureGroup;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\Actions\Action;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
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

class ProcedureGroupTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(ProcedureGroup::class)
            ->addActions([
                EditAction::make()
                    ->route('procedure-groups.edit'),
                DeleteAction::make()
                    ->route('procedure-groups.destroy'),
            ])
            ->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('operator', function (ProcedureGroup $item) {
                $actionDetail = '
                    <a  style = "margin-right: 10px;" data-bs-toggle="tooltip" data-bs-original-title="Chi tiết" href="'.route('procedure-groups.get-procedure-by-id', $item->id).'" class="btn btn-sm btn-icon btn-warning">
                        <i class="fa fa-eye"></i>
                    </a>
                ';

                $actionUpdate = '
                    <a  style = "margin-right: 10px;" data-bs-toggle="tooltip" data-bs-original-title="Sửa" href="'.route('procedure-groups.order.create', $item->id).'" class="btn btn-sm btn-icon btn-primary">
                            <span class="icon-tabler-wrapper">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-edit" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"></path>
                                    <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"></path>
                                    <path d="M16 5l3 3"></path>
                                </svg>
                            </span>
                        <span class="sr-only">Sửa</span>
                    </a>
                ';

                $actionTree = '
                    <a  style = "margin-right: 10px;"
                        data-bs-toggle="tooltip"
                        data-bs-original-title="Chi tiết" href="'.route('procedure-groups.get-procedure-flowchart-by-id', $item->id).'"
                        class="btn btn-sm btn-icon btn-Secondary"
                    >
                        <i class="fa-solid fa-diagram-project"></i>
                    </a>
                ';
                  // <button
                    //     data-bs-toggle="tooltip" data-bs-original-title="Sơ đồ"
                    //     id = "open-modal-view-flowchart"
                    //     type = "button"
                    //     style = "margin-right: 10px;"

                    //     data-target = "'.route('procedure-groups.get-procedure-flowchart-by-id', $item->id).'"
                    //     class="btn btn-sm btn-icon btn-Secondary"
                    // >
                    //     <i class="fa-solid fa-diagram-project"></i>
                    // </button>
                $checkStatus = $item->status == BaseStatusEnum::PUBLISHED && $this->hasPermission('procedure-groups.edit') ? true : false;
                $titleButton = $checkStatus ? 'Không hoạt động' : 'Hoạt động';
                $colorButton = $checkStatus ? 'danger' : 'success';
                $iconButton = $checkStatus ? '<i class="fa fa-lock"></i>' : '<i class="fa fa-unlock"></i>';

                $actionUpdateStatus = '
                    <button
                        data-bs-toggle="tooltip" data-bs-original-title="'.$titleButton.'"
                        id = "update-stauts"
                        type = "button"
                        style = "margin-right: 10px;"

                        data-target = "'.route('procedure-groups.update-status', $item->id ).'"
                        class="btn btn-sm btn-icon btn-'.$colorButton.'"
                    >
                        '.$iconButton.'
                    </button>
                ';

                return '
                <div class="table-actions" style= "display: flex;">
                    ' . $actionTree . '
                    ' . $actionDetail . '
                    ' . $actionUpdate . '
                    ' . $actionUpdateStatus . '
                 </div><div id="modalFlowchart"></div>
                ';
            })
            ->editColumn('status', function (ProcedureGroup $item){
                switch ($item['status']) {
                    case BaseStatusEnum::PUBLISHED:
                        return Html::tag('span', 'Hoạt động', ['class' => ' badge bg-success text-success-fg'])->toHtml();
                        break;
                    default:
                        return Html::tag('span', 'Không hoạt động', ['class' => ' badge bg-warning text-warning-fg'])->toHtml();
                        break;
                }
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
               'name',
               'code',
               'created_at',
               'status',
           ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            NameColumn::make()->hasPermission('procedure-groups.edit')->route('procedure-groups.edit'),
            Column::make('code')->title(trans('code')),
            CreatedAtColumn::make(),
            Column::make('status'),
            Column::make('operator')
            ->width(50)
            ->searchable(false)
            ->orderable(false),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('procedure-groups.create'), 'procedure-groups.create');
    }

    public function bulkActions(): array
    {
        return [
            // DeleteBulkAction::make()->permission('procedure-groups.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            // 'name' => [
            //     'title' => trans('core/base::tables.name'),
            //     'type' => 'text',
            //     'validate' => 'required|max:255',
            // ],
            // 'code' => [
            //     'title' => trans('code'),
            //     'type' => 'text',
            //     'validate' => 'required|max:255',
            // ],
            // 'status' => [
            //     'title' => trans('core/base::tables.status'),
            //     'type' => 'select',
            //     'choices' => BaseStatusEnum::labels(),
            //     'validate' => 'required|in:' . implode(',', BaseStatusEnum::values()),
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
