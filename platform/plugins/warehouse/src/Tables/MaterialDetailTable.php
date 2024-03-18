<?php

namespace Botble\Warehouse\Tables;

use Botble\Media\Facades\RvMedia;
use Botble\Table\Columns\Column;
use Botble\Warehouse\Actions\DetailAction;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\IdColumn;
use Botble\Warehouse\Models\QuantityMaterialStock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class MaterialDetailTable extends TableAbstract
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
            ->editColumn('warehouse_name', function (QuantityMaterialStock $item) {
                return $item->warehouse->name;
            })
            ->editColumn('material_name', function (QuantityMaterialStock $item) {
                return $item->material->name;
            })
            ->editColumn('material_batch', function (QuantityMaterialStock $item) {
                $material_batch = [];
                $warehouse = $item->warehouse->id;
                foreach ($item->material->materialBatches($warehouse)->get() as $batch) {
                    $material_batch[] = $batch->batch_code;
                }
                 return $material_batch;

            })
            ->editColumn('image', function (QuantityMaterialStock $item) {
                if ($item->material->image) {
                    return '<img src="' . RvMedia::getImageUrl($item->material->image) . '" width="80" height="80"/>';
                } else {
                    return '<img src = "https://phutungnhapkhauchinhhang.com/wp-content/uploads/2020/06/default-thumbnail.jpg" width="80" height="80" />';
                }

            })
            ->editColumn('unit', function (QuantityMaterialStock $item) {
                return $item->material->unit;
            })
            ->editColumn('status', function (QuantityMaterialStock $item) {
                return $item->warehouse->status->toHtml();
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
            ])->where(['material_id' => $materialStockId]);
        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            Column::make('warehouse_name')->title('Tên kho')->searchable(false)->orderable(false),
            Column::make('material_name')->title('Tên nguyên phụ liệu')->searchable(false)->orderable(false),
            Column::make('material_batch')->title('Nằm trong lô')->searchable(false)->orderable(false),
            Column::make('quantity')
                ->title('Số lượng tồn kho')
                ->orderable(false),
            Column::make('status')->title('Trạng thái kho')->searchable(false)->orderable(false),
        ];
    }


}
