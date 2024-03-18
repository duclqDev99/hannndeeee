<?php

namespace Botble\HubWarehouse\Http\Controllers\API;

use Botble\Base\Http\Controllers\BaseController;
use Botble\HubWarehouse\Http\Resources\HubResource;
use Botble\HubWarehouse\Http\Resources\ProductResource;
use Botble\HubWarehouse\Models\HubWarehouse;
use Botble\HubWarehouse\Models\QuantityProductInStock;

class HubWarehouseApiController extends BaseController
{
    public function getAllProductInHub()
    {
        $hubWarehouses = HubWarehouse::with('warehouseInHub.quantityInstock.product')->get();
        return HubResource::collection($hubWarehouses);
    }
    public function getProductOfHub($id)
    {
        $products = QuantityProductInStock::all();

        return response()->json($products, 200, );
    }
    public function getListHub()
    {
        $listHub = HubWarehouse::all();

        $hubArray = $listHub->map(function ($hub) {
            return [
                'id' => $hub->id,
                'name' => $hub->name,
            ];
        });

        return $hubArray->all();
    }
}
