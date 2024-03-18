<?php

namespace Botble\HubWarehouse\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Models\HubProduct;
use Botble\HubWarehouse\Models\HubUser;
use Botble\HubWarehouse\Models\QuantityProductInStock;
use Botble\HubWarehouse\Models\Warehouse;
use Botble\Showroom\Actions\CollapseAction;
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

class HubProductTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Product::class)
            ->addActions([
                CollapseAction::make(),
            ])->removeAllActions();
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
            })
            ->editColumn('collapse_action', function (Product $item) {
                return '<a class="show_collapse_btn" data-bs-toggle="collapse" role="button" data-id="' . $item->id . '">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                       <path fill-rule="evenodd" clip-rule="evenodd" d="M8.50842 7.00012L3.6709 11.8376L4.49586 12.6626L10.1583 7.00012L4.49586 1.33764L3.6709 2.1626L8.50842 7.00012Z" fill="#8E8E8E"/>
                    </svg>
                </a>';
            })
            ->setRowClass('on-loading position-relative')
            ->setRowAttr([
                'data-id' => fn ($item) => $item->id,
            ])
            ;



        return $this->toJson($data);
    }

    public function htmlDrawCallbackFunction(): string|null
    {
        return <<<JS
            let catches = new Array();
            const rows = this.api().data().rows().nodes();
            const loadingElement = '<div class="loading-spinner"></div>';

            $(rows).each(function(){
                const id = $(this).attr('data-id');
                const collapseId = `rowCollapse` + id;
                const collapseElement = document.createElement('tr');
                $(this).after(collapseElement);
            });

            $(document).on('show.bs.collapse', '.collapse', function () {
               $('.collapse.show').collapse('hide');
            });

            //Show collapse step
            $(document).off('click');
            $(document).on('click', '.show_collapse_btn', function(){
                const product_id = $(this).data('id');

                let _catch = catches.find(item => item == product_id);

                if(!_catch && !$(this).closest('tr').next().find('.collapse').hasClass('show')){
                    $(this).attr('disabled', true);
                    $(this).after(loadingElement);
                    $.ajax({
                        method: 'GET',
                        url: route('hub-product.detail', { id: product_id }),
                        contentType: 'html',
                        success: res => {
                            $(this).closest('tr').next().html(res);
                            $(this).closest('tr').next().find('.collapse').collapse('show');
                            $(this).next().remove();
                            $(this).removeAttr('disabled');
                            catches.push(product_id);
                            setTimeout(() => {
                                catches = catches.filter(item => item != product_id);
                            }, 10000);
                        }
                    });
                }
                else $(this).closest('tr').next().find('.collapse').collapse('toggle');
            });

        JS;
    }
    public function query(): Relation|Builder|QueryBuilder
    {
        if (!Auth::user()->hasPermission('hub-warehouse.all-permissions')) {
            $userHub = get_hub_for_user();

            $listWarehouse = $userHub->reduce(function ($carry, $hub) {
                return $carry->merge($hub->warehouseInHub->pluck('id'));
            }, collect());

            $listProduct = QuantityProductInStock::whereIn('stock_id', $listWarehouse)->pluck('product_id');
        } else {
            $listProduct = QuantityProductInStock::pluck('product_id');
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
                'start_date',
                'end_date',
                'quantity',
                'with_storehouse_management',
                'stock_status',
                'product_type',
            ])
            ->where('is_variation', 0)
            ->whereHas('variations', function ($query) use ($listProduct) {
                $query->whereIn('product_id', $listProduct);
            })
            ->with(['variations' => function ($query) use ($listProduct) {
                $query->whereIn('product_id', $listProduct);
            }])
            ->groupBy('id');


        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            CheckboxColumn::make(),
            Column::make('collapse_action')
            ->title('')
            ->orderable(false)
            ->searchable(false),
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
        return [

        ];
    }

    public function getBulkChanges(): array
    {
        return [

        ];
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
