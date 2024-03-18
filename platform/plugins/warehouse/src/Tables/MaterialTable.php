<?php

namespace Botble\Warehouse\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Media\Facades\RvMedia;
use Botble\Table\Columns\Column;
use Botble\Warehouse\Actions\DetailAction;
use Botble\Warehouse\Enums\MaterialStatusEnum;
use Botble\Warehouse\Models\Material;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\Warehouse\Actions\ExportMaterialAction;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class MaterialTable extends TableAbstract
{
    protected string $exportClass = ExportMaterialAction::class;

    public function setup(): void
    {
        $this
            ->model(Material::class)
            ->addActions([
                EditAction::make()
                    ->route('material.edit'),
                DetailAction::make()
                    ->route('material.detail'),
            ]);
        $this->view = 'plugins/warehouse::material.index';

    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('image', function (Material $item) {
                if ($item->image) {
                    return '<img src="' . RvMedia::getImageUrl($item->image) . '" width="80" height="80"/>';
                } else {
                    return '<img src = "' . RvMedia::getDefaultImage() . '" width="80" height="80" />';
                }
            })
            ->editColumn('name', function (Material $item) {
                if (!$this->hasPermission('material.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('material.edit', $item->getKey()), BaseHelper::clean($item->name));
            })
            ->editColumn('quantity', function (Material $item) {
                if ($item->quantity) {
                    if ($item->quantity - $item->min < 0) {
                        return '<strong class="text-danger"><u><i>' . $item->quantity .
                            '</u></i><strong>';
                    }
                    return $item->quantity ;
                }
                return 'null';

            })
            ->editColumn('price', function (Material $item) {
                    return format_price($item->price);
            })
            ->editColumn('type_material', function (Material $item) {
                if ($item->type_materials->isEmpty()) {
                    return '&mdash;';
                }
                $type_materials = [];
                foreach ($item->type_materials as $type_material) {
                    $type_materials[] = Html::link(route('type_material.edit', $type_material->id), $type_material->name);

                }
                return implode(', ', $type_materials);
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->with('type_materials')
            ->select([
                'id',
                'name',
                'image',
                'code',
                'min',
                'unit',
                'created_at',
                'status',
                'price',
            ]);
        $query->with('quantity_stock')
            ->selectSub(function ($query) {
                $query->from('wh_quantity_material_stock')
                    ->whereColumn('material_id', 'wh_materials.id')
                    ->selectRaw('SUM(quantity) as totalQuantity');
            }, 'quantity')
            ->orderByRaw('
                CASE
                    WHEN quantity IS NULL OR quantity = 0 OR min IS NULL THEN  1
                    ELSE 0
                END, (quantity - min) ASC
            ');
        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            NameColumn::make()->route('material.edit')->title('Tên')->searchable(true),
            Column::make('code')->title('Mã nguyên phụ liệu')->searchable(true),
            Column::make('image')->title('Hình ảnh')->searchable(false),
            Column::make('type_material')->title('Loại vật liệu')->searchable(false)->orderable(false),
            Column::make('min')->title('Số lượng tối thiểu')->searchable(false),
            Column::make('unit')->title('Đơn vị')->searchable(false),
            Column::make('quantity')->title('Số lượng')->searchable(false)->orderable(false),
            Column::make('price')->title('Giá')->searchable(false),
            StatusColumn::make('status')->title('Trạng thái')->searchable(false),
            CreatedAtColumn::make()->title('Ngày tạo')->searchable(false),
        ];
    }

    public function buttons(): array
    {
        $buttons = $this->addCreateButton(route('material.create'), 'material.create');
        $buttons['import-material'] = [
            'link' => '#',
            'text' => '<i class="fas fa-cloud-upload-alt"></i> Nhập excel',
            'class' => 'btn-success import-material',
        ];
        return $buttons;
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
            'name' => [
                'title' => 'Tên nguyên phụ liệu',
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'code' => [
                'title' => 'Mã nguyên phụ liệu',
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => MaterialStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', MaterialStatusEnum::values()),
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'date',
            ],
        ];
    }
    public function getDefaultButtons(): array
    {
        return [
            'export',
            'reload',
        ];
    }

}
