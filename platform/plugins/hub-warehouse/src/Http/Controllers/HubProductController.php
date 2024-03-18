<?php

namespace Botble\HubWarehouse\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Models\HubUser;
use Botble\HubWarehouse\Models\HubWarehouse;
use Botble\HubWarehouse\Models\QuantityProductInStock;
use Botble\HubWarehouse\Tables\HubProductTable;
use Illuminate\Support\Facades\Auth;


class HubProductController extends BaseController
{
    public function index(HubProductTable $table)
    {
        Assets::addScripts(['bootstrap-editable'])

            ->addStyles(['bootstrap-editable'])->addScriptsDirectly(
                [
                    'vendor/core/plugins/hub-warehouse/js/detail-product.js ',
                ]
            );
        PageTitle::setTitle(trans('Danh sách thành phẩm'));

        return $table->renderTable();
    }
    public function detail($id)
    {
        $products = Product::find($id);
        $productDetail = [

        ];
        foreach ($products->variations as $product) {
            $arrAttribute = $product->product->variationProductAttributes;
            list($color, $size, $stock, $total) = $this->extractColorAndSize($arrAttribute, $product->product->id);
            if ($stock) {
                $productDetail[] = [
                    'id' => $product->product->id,
                    'name' => $product->product->name,
                    'price' => $product->product->price,
                    'color' => $color,
                    'size' => $size,
                    'quantity' => $product->product->quantity,
                    'stock' => $stock,
                    'total' => $total
                ];
            }
        }
        return view('plugins/shared-module::product-detail.view-detail', compact('productDetail'));
    }
    private function extractColorAndSize($arrAttribute, $product_id)
    {
        $color = '';
        $size = '';
        $stock = [];
        $total = 0;
        $quantityInstocks = QuantityProductInStock::where('product_id', $product_id)->get();
        foreach ($quantityInstocks as $quantityInstock) {
            if (!Auth::user()->hasPermission('hub-warehouse.all-permissions')) {
                $warehouseUsers = HubUser::where('user_id', \Auth::id())->get();
                $warehouseIds = $warehouseUsers->pluck('hub_id')->toArray();
                if (in_array($quantityInstock->warehouse->id, $warehouseIds)) {
                    $stock[] = [
                        'stock' => $quantityInstock->warehouse->name . ' - ' . $quantityInstock->warehouse->hub->name,
                        'quantity' => $quantityInstock->quantity
                    ];
                    $total += $quantityInstock->quantity;
                }
            } else {
                $stock[] = [
                    'stock' => $quantityInstock->warehouse->name . ' - ' . $quantityInstock->warehouse->hub->name,
                    'hub' => $quantityInstock->warehouse->hub->name,
                    'quantity' => $quantityInstock->quantity
                ];
                $total += $quantityInstock->quantity;
            }
        }
        foreach ($arrAttribute as $attribute) {
            if ($attribute?->color) {
                $color = $attribute?->title;
            } else {
                $size = $attribute?->title;
            }
        }
        return [$color, $size, $stock, $total];
    }

}
