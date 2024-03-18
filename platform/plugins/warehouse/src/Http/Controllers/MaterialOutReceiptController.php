<?php

namespace Botble\Warehouse\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Models\AdminNotification;
use Botble\Warehouse\Enums\GoodsIssueEnum;
use Botble\Warehouse\Models\DetailBatchMaterial;
use Botble\Warehouse\Models\MaterialBatch;
use Botble\Warehouse\Models\MaterialOut;
use Botble\Warehouse\Models\MaterialOutConfirm;
use Botble\Warehouse\Tables\MaterialOutReceiptTable;
use Illuminate\Http\Request;
use Exception;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Warehouse\Enums\MaterialProposalStatusEnum;
use Botble\Warehouse\Models\ActualBatchMaterial;
use Botble\Warehouse\Models\Material;
use Botble\Warehouse\Models\MaterialProposalPurchase;
use Botble\Warehouse\Models\MaterialReceiptConfirm;
use Botble\Warehouse\Models\MaterialReceiptConfirmDetail;
use Botble\Warehouse\Models\QuantityMaterialStock;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaterialOutReceiptController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this
            ->breadcrumb()
            ->add(trans('Phiếu xuất kho'), route('goods-issue-receipt.index'));

    }
    public function index(MaterialOutReceiptTable $table)
    {

        return $table->renderTable();
    }

    public function receiptProposal(int|string $id, Request $request, BaseHttpResponse $response)
    {
        $requestData = $request->input();

        DB::beginTransaction();

        $proposal = MaterialProposalPurchase::where(['id' => $id])->first();

        $proposal->update([
            'status' => MaterialProposalStatusEnum::APPOROVED,
            'invoice_confirm_name' => Auth::user()->username,
            'date_confirm' => Carbon::now()->format('Y-m-d'),
        ]);

        $totalAmount = 0;
        $totalQuantity = 0;
        foreach ($requestData['material'] as $key => $material) {
            $totalAmount += ($material['quantity'] * $material['material_price']);
            $totalQuantity += ((int) $material['quantity']);
        }

        $dataReceipt = [
            'warehouse_id' => $proposal->warehouse_id,
            'invoice_issuer_name' => Auth::user()->username,
            'proposal_id' => $proposal->id,
            'warehouse_name' => $proposal->warehouse_name,
            'warehouse_address' => $proposal->warehouse_address,
            'quantity' => $totalQuantity,
            'total_amount' => (int) $totalAmount,
            'title' => $proposal->title,
            'description' => $proposal->description,
            'expected_date' => $proposal->expected_date,
        ];

        try {
            $materialReceipt = MaterialReceiptConfirm::create($dataReceipt);
            event(new CreatedContentEvent(MATERIAL_RECEIPT_PURCHASE_MODULE_SCREEN_NAME, $request, $materialReceipt));
        } catch (Exception $err) {
            DB::rollBack();
        }

        for ($i = 0; $i < count($requestData['material']); $i++) {
            $dataInsert = [
                'receipt_id' => $materialReceipt->id,
                'supplier_name' => $proposal->proposalDetail[$i]->supplier_name,
                'material_code' => $proposal->proposalDetail[$i]->material_code,
                'material_name' => $proposal->proposalDetail[$i]->material_name,
                'material_unit' => $proposal->proposalDetail[$i]->material_unit,
                'material_quantity' => $requestData['material'][$proposal->proposalDetail[$i]->id]['quantity'],
                'material_price' => $requestData['material'][$proposal->proposalDetail[$i]->id]['material_price'],
                'is_old_material' => $proposal->proposalDetail[$i]->is_old_material,
                'material_id' => $proposal->proposalDetail[$i]->material_id,
            ];

            try {
                MaterialReceiptConfirmDetail::create($dataInsert);
            } catch (Exception $err) {
                DB::rollBack();
            }
        }

        //DB commit
        DB::commit();

        //Create notification for stock manager
        AdminNotification::query()->create([
            'title' => $proposal->title,
            'action_label' => 'Xem',
            // 'action_url' => route('receipt.code', $requestData['proposal_code']),
            'description' => $requestData['description'],
            'permission' => 'receipt.code',
        ]);

        return $response
            ->setPreviousUrl(route('material-proposal-purchase.index'))
            ->setNextUrl(route('material-proposal-purchase.index'))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function confirmReceipt(int|string $id, Request $request, BaseHttpResponse $response)
    {

        Assets::addStylesDirectly(['vendor/core/plugins/warehouse/css/goods-issue.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/ecommerce/js/order.js',
                'vendor/core/plugins/warehouse/js/qr-scan-confirm-out.js',
                'vendor/core/plugins/warehouse/js/qr-scan-confirm-out-pc.js',
            ])
            ->addScripts(['blockui', 'input-mask']);

        $goodIssue = MaterialOutConfirm::where(['id' => $id])
            ->with(['proposalOutDetail', 'proposalOutDetail.materialBatch' => function ($query) use ($id) {
                $warehouseId = MaterialOutConfirm::where('id', $id)->pluck('warehouse_id')->first();
                $query->where('stock_id', $warehouseId)->where('quantity', '>', 0);
            }])
            ->first();
        if ($goodIssue->status->toValue() != GoodsIssueEnum::PENDING) {

            abort_if($goodIssue->status != 'pending', 403, 'Đã nhập kho');
        }
        $this->pageTitle(__('Xác nhận xuất kho'));
        return view('plugins/warehouse::material.receipt.receipt-out', compact('goodIssue'));
    }
    public function confirmGoodIssue(int|string $id, Request $request, BaseHttpResponse $response)
    {

        $requetsData = $request->input();
        if (!isset($requetsData['material'])) {
            return $response
                ->setError()
                ->setMessage('Trong kho không còn tất cả nguyên phụ liệu mà bạn muốn!!!!');
        }
        $goodIssue = MaterialOutConfirm::find($id);
        DB::beginTransaction();
        try {

            $goodIssue->update([
                'status' => 'confirm',
                'invoice_confirm_name' => Auth::user()->name,
                'date_confirm' => now()
            ]);
            $proposalGoodIssue = MaterialOut::find($goodIssue->proposal_id);
            $proposalGoodIssue->update(['status' => 'confirm']);
            foreach ($requetsData['material'] as $key => $material) {
                $dataInsert = [
                    'reason' => $material['reason'],
                    'quantity' => $material['quantity'],
                    'confirm_detail_id' => $key,
                    'material_id' => $material['material_id'],
                    'material_code' => $material['material_code'],
                ];
                $actualBatchMaterial = ActualBatchMaterial::query()->create($dataInsert);
                $materials = Material::find((int) $material['material_id']);
                $materialWarehouse = QuantityMaterialStock::where('warehouse_id', $requetsData['warehouse_id'])->where(
                    'material_id',
                    $materials->id,
                )->first();
                foreach ($requetsData['materialDetai'] as $key => $value) {
                    if ($value['issueMaterial'] == $actualBatchMaterial->confirm_detail_id) {
                        $materialBatch = MaterialBatch::where('id', $key)->first();
                        $quantityBatch = (int) $materialBatch->quantity - (int) $value['quantity_actual'];
                        if ($quantityBatch < 0) {
                            DB::rollBack();
                            return $response
                                ->setError()
                                ->setMessage('Trong lô đã hết sản phẩm ' . $materials->name);
                        }
                        $materialBatch->update(['quantity' => $quantityBatch]);
                        $dataInsertDeatail = [
                            'quantity_actual' => $value['quantity_actual'],
                            'quantity' => $value['quantity_actual'],
                            'batch_code' => $materialBatch->batch_code,
                            'actual_out_detail_id' => $actualBatchMaterial->id,
                        ];

                        DetailBatchMaterial::query()->create($dataInsertDeatail);
                    }
                }

                $quantityWarehouse = (int) $materialWarehouse->quantity - (int) $material['quantity'];
                if ($quantityWarehouse < 0) {
                    DB::rollBack();
                    return $response
                        ->setError()
                        ->setMessage('Trong kho đã hết sản phẩm ' . $materials->name);
                }
                $materialWarehouse->update(['quantity' => $quantityWarehouse]);
            }
            $arrNoti = [
                'action' => 'xác nhận',
                'permission' => "goods-issue-receipt.receipt",
                'route' => route('goods-issue-receipt.view', $goodIssue->id),
                'status' => 'xác nhận'
            ];
            send_notify_cms_and_tele($goodIssue, $arrNoti);
            DB::commit();
            return $response
                ->setPreviousUrl(route('goods-issue-receipt.index'))
                ->setNextUrl(route('goods-issue-receipt.index'))
                ->setMessage(trans('core/base::notices.create_success_message'));
        } catch (Exception $exception) {
            DB::rollBack();
            dd($exception->getMessage());
            return $response
                ->setError()
                ->setMessage('Có lỗi xảy ra khi thực hiện tác vụ');
        }
    }


    public function viewReceiptById(int|string $id, BaseHttpResponse $response)
    {
        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css']);

        $receipt = MaterialOutConfirm::where(['id' => $id])->with('proposalOutDetail.actualBatchMaterial')->first();

        $this->pageTitle(trans('Chi tiết phiếu :code', ['code' => $receipt->title]));

        return view('plugins/warehouse::material.receipt.view-out', compact('receipt'));
    }
    public function getMoreQuantity(Request $request)
    {
        $requestData = $request->input();
        $quantity = min($requestData['quantity'], $requestData['quantityStock']);
        $materialBatches = MaterialBatch::where('material_id', $requestData['material_id'])->where('stock_id', $requestData['warehouse_id'])->where('quantity', '>', 0)
            ->get();
        $selectedBatches = [];
        $remainingQuantity = $quantity;
        $material = Material::find($requestData['material_id']);
        foreach ($materialBatches as $batch) {
            if ($batch->quantity >= $remainingQuantity) {
                $selectedBatches[] = [
                    'batch_id' => $batch->id,
                    'batch_quantity' => $batch->quantity,
                    'quantity' => $remainingQuantity,
                    'batch_code' => $batch->batch_code,
                    'quantityStock' => $requestData['quantityStock'],
                    'material_id' => $material->id,
                    'material_name' => $material->name,
                    'material_code' => $material->code,
                    'img' => $material->image,
                ];

                break;
            } else {
                $selectedBatches[] = [
                    'batch_id' => $batch->id,
                    'batch_quantity' => $batch->quantity,
                    'quantity' => $batch->quantity,
                    'batch_code' => $batch->batch_code,
                    'quantityStock' => $requestData['quantityStock'],
                    'material_name' => $material->name,
                    'material_code' => $material->code,
                    'material_id' => $material->id,
                    'img' => $material->image,
                ];
                $remainingQuantity -= $batch->quantity;
            }
        }
        return ($selectedBatches);
    }
}
