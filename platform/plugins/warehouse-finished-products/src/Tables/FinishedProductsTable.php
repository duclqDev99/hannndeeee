<?php

namespace Botble\WarehouseFinishedProducts\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Enums\ProductTypeEnum;
use Botble\Ecommerce\Enums\StockStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\ImageColumn;
use Botble\WarehouseFinishedProducts\Actions\DetailAction;
use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;
use Botble\WarehouseFinishedProducts\Models\WarehouseUser;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Model;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Blade;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class FinishedProductsTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Product::class)
            ->addActions([
                DetailAction::make()
            ]);

    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (Product $item) {
                $productType = null;

                if (EcommerceHelper::isEnabledSupportDigitalProducts()) {
                    $productType = Html::tag('small', ' &mdash; ' . $item->product_type->label())->toHtml();
                }

                if (!$this->hasPermission('products.edit')) {
                    return BaseHelper::clean($item->name) . $productType;
                }

                return Html::link(
                    route('products.edit', $item->getKey()),
                    BaseHelper::clean($item->name)
                ) . $productType;
            })
            ->editColumn('price', function (Product $item) {
                return $item->price_in_table;
            })
            ->editColumn('quantity', function (Product $item) {
                $quantity = 0;
                foreach ($item->variations as $value) {
                    $quantity += $value->product->quantity;
                }
                return $quantity;
            })

            ->editColumn('attribute', function (Product $item) {
                $attributePairs = [];

                foreach ($item->variations as $variation) {
                    if ($variation->productAllStock->isNotEmpty()) {
                        $colorAttribute = $variation->productAttributes->where('attribute_set_id', 1)->first();
                        $sizeAttribute = $variation->productAttributes->where('attribute_set_id', 2)->first();
                        if ($colorAttribute && $sizeAttribute) {
                            $attributePairs[] = "{$colorAttribute->title} {$sizeAttribute->title}";
                        }
                    }

                }
                return implode('<br>', $attributePairs);
            })

            ->editColumn('order', function (Product $item) {
                return view('plugins/ecommerce::products.partials.sort-order', compact('item'))->render();
            })
            ->editColumn('stock_status', function (Product $item) {
                return BaseHelper::clean($item->stock_status_html);
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->getModel()
            ->query()
            ->select([
                'id',
                'name',
                'order',
                'created_at',
                'status',
                'sku',
                'image',
                'images',
                'price',
                'sale_price',
                'sale_type',
                'start_date',
                'end_date',
                'quantity',
                'with_storehouse_management',
                'stock_status',
                'product_type',
            ])
            ->where('is_variation', 0)->whereHas('productBatches',function($q){
                $q->where('warehouse_type',WarehouseFinishedProducts::class);
            });

        if (!$this->hasPermission('warehouse-finished-products.warehouse-all')) {
            $warehouseUsers = WarehouseUser::where('user_id', \Auth::id())->get();
            $warehouseIds = $warehouseUsers->pluck('warehouse_id')->toArray();
            $query->whereHas('productBatches', function ($q) use ($warehouseIds) {
                $q->whereIn('warehouse_id', $warehouseIds);
            });
        }
        return $this->applyScopes($query);
    }
    public function htmlDrawCallbackFunction(): string|null
    {
        return parent::htmlDrawCallbackFunction() . '$(".editable").editable({mode: "inline"});';
    }
    public function columns(): array
    {
        return [
            IdColumn::make(),
            ImageColumn::make()->title('Hình ảnh'),
            Column::make('name')
                ->title('Tên')
                ->alignStart(),
            Column::make('price')
                ->title('Giá')
                ->alignStart(),
            Column::make('quantity')
                ->title('Số lượng')
                ->alignStart(),
            Column::make('sku')
                ->title('Mã thành phẩm')
                ->alignStart(),
            Column::make('attribute')
                ->title('Thuộc tính')
                ->alignStart(),
            StatusColumn::make(),
        ];
    }
    public function bulkActions(): array
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
            'order' => [
                'title' => trans('core/base::tables.order'),
                'type' => 'number',
                'validate' => 'required|min:0',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => BaseStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', BaseStatusEnum::values()),
            ],
            'category' => [
                'title' => trans('plugins/ecommerce::products.category'),
                'type' => 'select-ajax',
                'validate' => 'required',
                'callback' => function (int|string|null $value = null): array {
                    $categorySelected = [];
                    if ($value && $category = ProductCategory::query()->find($value)) {
                        $categorySelected = [$category->getKey() => $category->name];
                    }

                    return [
                        'url' => route('product-categories.search'),
                        'selected' => $categorySelected,
                        'minimum-input' => 1,
                    ];
                },
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
            ],
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
            ],
        ];
    }
}
