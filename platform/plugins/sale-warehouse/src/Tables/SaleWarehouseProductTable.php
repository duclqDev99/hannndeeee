<?php

namespace Botble\SaleWarehouse\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Models\HubProduct;
use Botble\HubWarehouse\Models\HubUser;
use Botble\HubWarehouse\Models\Warehouse;
use Botble\SaleWarehouse\Models\SaleProduct;
use Botble\SaleWarehouse\Models\SaleWarehouseChild;
use Botble\Showroom\Actions\CollapseAction;
use Botble\Showroom\Models\ShowroomProduct;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CheckboxColumn;
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
use Yajra\DataTables\Html\Columns\Checkbox;

class SaleWarehouseProductTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Product::class)
            ->addActions([
                CollapseAction::make(),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('attribute', function (Product $item) {
                $attributes = [];

                foreach ($item->variations as $key => $variation) {
                    $arrAttribute = $variation->product->variationProductAttributes;

                    foreach ($arrAttribute as $attribute) {
                        if ($attribute?->color) {
                            $attributes[$key]['color'] = $attribute?->title;
                        } else {
                            $attributes[$key]['size'] = $attribute?->title;
                        }
                    }
                }

                // Sort color and size arrays for each variation
                $result = [];

                foreach ($attributes as $key => $values) {
                    $colorLabel = isset($values['color']) ? 'Color: ' : '';
                    $sizeLabel = isset($values['size']) ? 'Size: ' : '';
                    $colorValues = isset($values['color']) ? $values['color'] : '';
                    $sizeValues = isset($values['size']) ? $values['size'] : '';

                    $result[] = $colorLabel . $colorValues . ' ' . $sizeLabel . $sizeValues;
                }

                return implode('<br>', $result);
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        if (!Auth::user()->hasPermission('sale-warehouse.all')) {
            $userSale = get_list_sale_warehouse_id_for_current_user();
            $listProduct = SaleProduct::whereIn('warehouse_id', $userSale)->pluck('product_id');
        } else {
            $listProduct = SaleProduct::pluck('product_id');
        }

        $query = $this
            ->getModel()
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
                'stock_status',
                'product_type',
            ])
            ->where('is_variation', 0)
            ->whereHas('variations', function ($query) use ($listProduct) {
                // Ensure the product has variations in the listProduct
                $query->whereIn('product_id', $listProduct);
            })
            ->with(['variations' => function ($query) use ($listProduct) {
                // Attempt to load variations within the listProduct
                $query->whereIn('product_id', $listProduct);
            }])
            ->groupBy('id');


        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            CheckboxColumn::make(),
            IdColumn::make(),
            NameColumn::make(),
            Column::make('sku')
                ->title('Mã thành phẩm')
                ->alignStart(),
            Column::make('attribute')
                ->title('Thuộc tính')->orderable(false)->searchable(false)
                ->alignStart(),
            StatusColumn::make(),
        ];
    }

    public function buttons(): array
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
    public function getCheckboxColumnHeading(): array
    {
        return [];
    }

    public function getFilters(): array
    {
        return $this->getBulkChanges();
    }
}
