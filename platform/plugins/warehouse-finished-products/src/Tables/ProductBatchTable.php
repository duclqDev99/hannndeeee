<?php

namespace Botble\WarehouseFinishedProducts\Tables;

use Botble\ACL\Models\User;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Ecommerce\Models\Product;
use Botble\ProductQrcode\Enums\QRStatusEnum;
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
use Botble\Warehouse\Models\Supplier;
use Botble\WarehouseFinishedProducts\Enums\ProductBatchStatusEnum;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProductBatchTable extends TableAbstract
{

    public function setup(): void
    {
        $this
            ->model(ProductBatch::class)
            ->addActions([
                EditAction::make()
                    ->route('proposal-receipt-products.edit'),
                DeleteAction::make()
                    ->route('proposal-receipt-products.destroy'),
            ])
            ->removeAllActions();
        ;

    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('list_product', function (ProductBatch $item) {
                // $totalQuantities = $item->listProduct
                //     ->map(function ($product) {
                //     return $product->statusQrCode->where('status',QRStatusEnum::INSTOCK);
                // });
                $totalQuantities = $item->listProduct
                    ->groupBy('product_id')
                    ->map(function ($group) {
                    return $group->count();
                });
                $productsInfo = $totalQuantities->map(function ($quantity, $productId) {
                    $product = Product::find($productId);
                    $attr = [];
                    foreach ($product->variationProductAttributes as $attribute) {

                        $attr[] = $attribute->title;
                    }
                    if ($quantity > 0) {
                        return $product->name . ' - ' . implode(' , ', $attr) . ': số lượng ' . $quantity;
                    }
                });
                $result = $productsInfo->filter()->implode('<br>');
                return $result ?: 'Đã hết sản phẩm';
            })
            ->editColumn('total_quantity', function (ProductBatch $item) {

                return $item->quantity;
            })

        ;

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        if (isset(request()->input()['id'])) {
            $productStockId = request()->input()['id'];
        }
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'receipt_id',
                'batch_code',
                'quantity',
                'start_qty',
                'status',
                'warehouse_id',
                'warehouse_type',
                'product_parent_id',
                'created_at'
            ])
            ->where(['warehouse_type' => WarehouseFinishedProducts::class, 'warehouse_id' => $productStockId, 'status' => ProductBatchStatusEnum::INSTOCK])
        ;
        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            Column::make('batch_code')
                ->title('Mã lô hàng')
                ->orderable(false)
                ->searchable(false),
            Column::make('list_product')->title('Danh sách sản phẩm')->orderable(false)
                ->searchable(false),
            Column::make('quantity')
                ->title('Số lượng còn lại trong lô'),
            Column::make('start_qty')
                ->title('Số lượng đầu'),
            CreatedAtColumn::make()->dateFormat('d-m-Y')->title('Ngày nhập')
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
        return [];
    }

    public function getBulkChanges(): array
    {
        return [];
    }

    public function getFilters(): array
    {
        return [
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
