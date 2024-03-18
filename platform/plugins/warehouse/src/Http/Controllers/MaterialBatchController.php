<?php

namespace Botble\Warehouse\Http\Controllers;

use Botble\Warehouse\Http\Requests\InventoryMaterialRequest;
use Botble\Warehouse\Models\MaterialWarehouse;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;
use Illuminate\Http\Request;
use Exception;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Warehouse\Forms\InventoryMaterialForm;
use Botble\Base\Forms\FormBuilder;
use Botble\Warehouse\Models\MaterialBatch;
use Botble\Warehouse\Tables\MaterialBatchTable;
use Botble\Warehouse\Tables\MaterialStockBatchTable;
use Botble\Base\Facades\Assets;

class MaterialBatchController extends BaseController
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans('Lô hàng của kho'));
    }
    public function index(MaterialStockBatchTable $table)
    {

        return $table->renderTable();
    }

    public function detailBatchInStock(int|string $id, Request $request, MaterialBatchTable $table)
    {
        // $this->pageTitle(trans('Chi tiết lô hàng trong kho :name', ['name' => WarehouseFinishedProducts::find($id)->name]));
        $request->merge(['id' => $id]);

        Assets::addScriptsDirectly([
            'vendor/core/plugins/warehouse/js/material-batch-qr-scan.js',
            'vendor/core/plugins/warehouse/js/material-batch-qr-scan-pc.js'
        ]);

        return $table->render('plugins/warehouse::material-batch/table/detail');

        // return $table->renderTable();
    }

    public function qrScan(Request $request)
    {
        $materialBatch = MaterialBatch::with('material:id,name', 'receipt:id,invoice_confirm_name')->where('batch_code', $request->batch_code)->first();
        if ($request->viewport == 'mobile')
            return view('plugins/warehouse::material-batch/ajax/scan-content', compact('materialBatch'));

        try {
            if (!$materialBatch)
                throw new \Exception('Mã QR không tồn tại trên hệ thống');
            return response()->json([
                'success' => 1,
                'message' => 'successfully',
                'data' => $materialBatch
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => 0,
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
