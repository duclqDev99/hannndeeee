<?php

namespace Botble\Warehouse\Http\Controllers\API;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Warehouse\Enums\MaterialStatusEnum;
use Botble\Warehouse\Models\Stock;
use Botble\Warehouse\Models\Agency;
use Botble\Ecommerce\Models\Product;
use Botble\Warehouse\Enums\StockStatusEnum;
use Botble\Warehouse\Models\MaterialWarehouse;
use Botble\Warehouse\Models\QuantityMaterialStock;

class WarehouseApiController extends BaseController
{
    public function getAllWarehouseMaterial()
    {
        $warehouse = MaterialWarehouse::where(['status' => StockStatusEnum::ACTIVE])
            ->with(['materials' => function ($query) {
                $query->where('status', MaterialStatusEnum::ACTIVE);
            }])
            ->get();

        if (!empty($warehouse)) {
            return $this->responseApi(0, "Thành công!", 200, $warehouse);
        } else {
            return $this->responseApi(1, "Không tìm thấy bất kỳ kho nguyên liệu nào!", 200);
        }
    }

    public function getAllMaterialInStock()
    {
        $warehouse = QuantityMaterialStock::get();

        if (!empty($warehouse)) {
            return $this->responseApi(0, "Thành công!", 200, $warehouse);
        } else {
            return $this->responseApi(1, "Không tìm thấy bất kỳ kho nguyên liệu nào!", 200);
        }
    }

    public function responseApi($errorCode, $msg, $httpCode, $data = null)
    {
        $dataRes = [
            'error_code' => $errorCode,
            'msg' => $msg,
            'body' => $data,
        ];

        return response()->json($dataRes, $httpCode);
    }
}
