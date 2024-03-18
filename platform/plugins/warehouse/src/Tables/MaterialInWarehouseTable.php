<?php

namespace Botble\Warehouse\Tables;

use Botble\ACL\Models\User;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Media\Facades\RvMedia;
use Botble\Warehouse\Enums\BaseStatusEnum;
use Botble\Table\Columns\Column;
use Botble\Warehouse\Actions\DetailAction;
use Botble\Warehouse\Models\MaterialOut;
use Botble\Warehouse\Models\MaterialBatch;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\Warehouse\Models\QuantityMaterialStock;
use Botble\Warehouse\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class MaterialInWarehouseTable extends TableAbstract
{

    public function setup(): void
    {
        $this
            ->model(QuantityMaterialStock::class)
            ->addActions([
                DetailAction::make()
                    ->route('proposal-goods-issue.edit'),
            ])
            ->removeAllActions();
        ;

    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (QuantityMaterialStock $item) {
                return $item->material->name;
            })
            ->editColumn('image', function (QuantityMaterialStock $item) {
                if ($item->material->image) {
                    return '<img src="' . RvMedia::getImageUrl($item->material->image) . '" width="80" height="80"/>';
                } else {
                    return '<img src = "' . RvMedia::getDefaultImage() . '" width="80" height="80" />';
                }

            })
            ->editColumn('unit', function (QuantityMaterialStock $item) {
                return $item->material->unit;
            })
            ->editColumn('status', function (QuantityMaterialStock $item) {
                return $item->material->status->toHtml();
            })
            ->editColumn('code', function (QuantityMaterialStock $item) {
                return $item->material->code;
            })
            ->editColumn('price', function (QuantityMaterialStock $item) {
                return $item->material->price;
            })


        ;

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $materialStockId = get_material_stock_id_by_request();

        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'quantity',
                'warehouse_id',
                'material_id',
            ])->where(['warehouse_id' => $materialStockId]);
        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            Column::make('name')->title('Tên sản phẩm')->searchable(false)->orderable(false),
            Column::make('image')->title('Hình ảnh')->searchable(false)->orderable(false),
            Column::make('code')->title('Mã sản phẩm')->searchable(false)->orderable(false),
            Column::make('unit')->title('Đơn vị')->searchable(false)->orderable(false),
            Column::make('price')->title('Giá / 1 đơn vị')->searchable(false)->orderable(false),
            Column::make('quantity')
            ->title('Số lượng tồn kho'),
            Column::make('status')->title('Trạng thái NPL')
            ->orderable(false)->searchable(false),
        ];
    }


}
