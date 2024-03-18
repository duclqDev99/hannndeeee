<?php

namespace Botble\Warehouse\Http\Controllers\API;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Warehouse\Models\DetailBatchMaterial;
use Botble\Warehouse\Models\Material;
use Botble\Warehouse\Models\MaterialBatch;
use Botble\Warehouse\Models\MaterialOut;
use Botble\Warehouse\Models\MaterialOutConfirm;
use Botble\Warehouse\Models\MaterialOutDeatail;
use Botble\Warehouse\Models\MaterialWarehouse;
use Botble\Warehouse\Models\ProcessingHouse;
use Botble\Warehouse\Models\QuantityMaterialStock;

class WarehouseMaterialApiController extends BaseController
{
    public function getAllWarehouse()
    {
        $warehouse = MaterialWarehouse::where('status','active')->get();
        return response()->json(['process' => $warehouse], 200);

    }
    public function getWarehouseAll()
    {
        $warehouse = MaterialWarehouse::all();
        return response()->json(['data' => $warehouse], 200);
    }
    public function getListMaterialProposalOut($id)
    {

        $materialsDetails = MaterialOutDeatail::where('proposal_id', $id)->with('materials', 'materialOut')->get();

        $materialsDetails->each(function ($materialDetail) {
            $materials = $materialDetail->materialOut;
            $quantity = QuantityMaterialStock::where('warehouse_id', $materialDetail->materialOut->warehouse_id)->where('material_id', $materialDetail->materials->id)->first();
            $materialDetail->quantity = $quantity ? $quantity->quantity : 0;
        });


        return response()->json(['data' => $materialsDetails], 200);
    }

    public function getPriceMaterial($id, $warehose_id)
    {
        $material = Material::find($id);
        $quantity = QuantityMaterialStock::where('warehouse_id', $warehose_id)->where('material_id', $id)->first();
        if ($material) {
            return response()->json(
                [
                    'price' => $material->price,
                    'quantity' => $quantity->quantity,
                    'material_code' => $material->code,
                    'unit' => $material->unit,
                ]
            );
        }
    }

}
