<?php

namespace Botble\Warehouse\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Table\Columns\Column;
use Botble\Warehouse\Models\TypeMaterial;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class TypeMaterialTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(TypeMaterial::class)
            ->addActions([
                EditAction::make()
                    ->route('type_material.edit'),
                DeleteAction::make()
                    ->route('type_material.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (TypeMaterial $item) {
                if (!$this->hasPermission('type_material.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('type_material.edit', $item->getKey()), BaseHelper::clean($item->name));
            })
            ->editColumn('parent_id', function (TypeMaterial $item) {
                if ($item->parent_id > 0) {
                    $type = TypeMaterial::find($item->parent_id);
                    return $type->name;
                } else {
                    return '---';
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
                'parent_id',
                'created_at',
                'status',
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            NameColumn::make()->title('Tên loại')->route('type_material.edit'),
            Column::make('parent_id')->title('Tên loại cha'),
            CreatedAtColumn::make()->title('Ngày tạo'),
            StatusColumn::make()->title('Trạng thái'),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('type_material.create'), 'type_material.create');
    }
    public function getCheckboxColumnHeading(): array
    {
        return [];
    }
    // public function bulkActions(): array
    // {
    //     return [
    //         DeleteBulkAction::make()->permission('type_material.destroy'),
    //     ];
    // }

    public function getBulkChanges(): array
    {
        return [

        ];
    }

    public function getFilters(): array
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
}
