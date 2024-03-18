<?php

namespace Botble\Warehouse\Http\Controllers;

use Botble\Base\Facades\Assets;
use Illuminate\Http\Request;
use Exception;
use Botble\Warehouse\Tables\MaterialReceiptConfirmTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Warehouse\Enums\MaterialProposalStatusEnum;
use Botble\Warehouse\Enums\MaterialReceiptStatusEnum;
use Botble\Warehouse\Models\ActualReceipt;
use Botble\Warehouse\Models\ActualReceiptDetail;
use Botble\Warehouse\Models\Material;
use Botble\Warehouse\Models\MaterialBatch;
use Botble\Warehouse\Models\MaterialProposalPurchase;
use Botble\Warehouse\Models\MaterialReceiptConfirm;
use Botble\Warehouse\Models\MaterialReceiptConfirmDetail;
use Botble\Warehouse\Models\QuantityMaterialStock;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MaterialReceiptConfirmController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this
            ->breadcrumb()
            ->add(trans('phiếu nhập kho'), route('material-receipt-confirm.index'));
    }
    public function index(MaterialReceiptConfirmTable $table)
    {
        Assets::addScriptsDirectly([
            'vendor/core/plugins/warehouse/js/print-qr-code.js',
        ]);


        return $table->renderTable();
    }

    public function receiptProposal(int|string $id, Request $request, BaseHttpResponse $response)
    {
        $requestData = $request->input();
        $expectedDate = date('Y-m-d', strtotime($requestData['expected_date']));

        DB::beginTransaction();

        $proposal = MaterialProposalPurchase::where(['id' => $id])->first();

        if (empty($proposal)) {
            return $response
                ->setPreviousUrl(route('material-proposal-purchase.index'))
                ->setNextUrl(route('material-proposal-purchase.index'))
                ->setError()
                ->setMessage('Không tìm thấy đơn đề xuất này!!');
        }

        $currentDate = Carbon::now()->format('Y-m-d');
        if ($expectedDate < $currentDate) {
            return $response
                ->setPreviousUrl(route('material-proposal-purchase.index'))
                ->setNextUrl(route('material-proposal-purchase.receipt', $id))
                ->setError()
                ->setMessage('Vui lòng nhập ngày dự kiến lớn hơn ngày hiện tại!');
        }

        $proposal->update([
            'status' => MaterialProposalStatusEnum::APPOROVED,
            'invoice_confirm_name' => Auth::user()->name,
            'date_confirm' => Carbon::now()->format('Y-m-d'),
        ]);

        $totalAmount = 0;
        $totalQuantity = 0;
        foreach ($requestData['material'] as $key => $material) {
            $totalAmount += ($material['quantity'] * $material['material_price']);
            $totalQuantity += ((int) $material['quantity']);
        }

        $dataReceipt = [
            'general_order_code' => $proposal->general_order_code,
            'warehouse_id' => $proposal->warehouse_id,
            'invoice_issuer_name' => Auth::user()->name,
            'proposal_id' => $proposal->id,
            'warehouse_name' => $proposal->warehouse_name,
            'warehouse_address' => $proposal->warehouse_address,
            'wh_departure_id' => $proposal->wh_departure_id,
            'wh_departure_name' => $proposal->wh_departure_name,
            'is_from_supplier' => $proposal->is_from_supplier,
            'quantity' => $totalQuantity,
            'total_amount' => (int) $totalAmount,
            'title' => $proposal->title,
            'description' => $requestData['description'],
            'expected_date' => $expectedDate,
        ];

        try {
            $materialReceipt = MaterialReceiptConfirm::query()->create($dataReceipt);
            event(new CreatedContentEvent(MATERIAL_RECEIPT_PURCHASE_MODULE_SCREEN_NAME, $request, $materialReceipt));

            //Notify for stock
            $arrNoti = [
                'action' => 'tạo',
                'permission' => "material-receipt-confirm.confirm",
                'route' => route('material-receipt-confirm.confirm', $materialReceipt->id),
                'status' => 'Chờ nhập kho'
            ];
            send_notify_cms_and_tele($materialReceipt, $arrNoti);

            //Notify for creator proposal
            $arrNotiCreator = [
                'action' => 'duyệt',
                'permission' => "material-proposal-purchase.create",
                'route' => route('material-proposal-purchase.view.code', $proposal->id),
                'status' => 'Đã duyệt'
            ];
            send_notify_cms_and_tele($proposal, $arrNotiCreator);
        } catch (Exception $err) {
            throw new Exception($err->getMessage(), 1);
            DB::rollBack();
        }

        $idReceipt = $materialReceipt->id;

        for ($i = 0; $i < count($requestData['material']); $i++) {
            $dataInsert = [
                'receipt_id' => $idReceipt,
                'supplier_id' => $proposal->proposalDetail[$i]->supplier_id,
                'supplier_name' => $proposal->proposalDetail[$i]->supplier_name,
                'material_code' => $proposal->proposalDetail[$i]->material_code,
                'material_name' => $proposal->proposalDetail[$i]->material_name,
                'material_unit' => $proposal->proposalDetail[$i]->material_unit,
                'material_quantity' => $requestData['material'][$proposal->proposalDetail[$i]->id]['quantity'],
                'material_price' => $requestData['material'][$proposal->proposalDetail[$i]->id]['material_price'],
                'material_id' => $proposal->proposalDetail[$i]->material_id,
            ];

            try {
                MaterialReceiptConfirmDetail::create($dataInsert);
            } catch (Exception $err) {
                DB::rollBack();
                throw new Exception($err->getMessage(), 1);
            }
        }

        //DB commit
        DB::commit();

        return $response
            ->setPreviousUrl(route('material-proposal-purchase.index'))
            ->setNextUrl(route('material-proposal-purchase.index'))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function confirmReceipt(int|string $id)
    {
        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                    'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                    'vendor/core/plugins/ecommerce/js/order.js',
                ])
            ->addScripts(['blockui', 'input-mask']);

        $receipt = MaterialReceiptConfirm::where(['id' => $id])->with('receiptDetail')->first();

        abort_if($receipt->status == MaterialReceiptStatusEnum::APPOROVED, 403);

        $this->pageTitle(__('Xác nhận nhập kho'));

        return view('plugins/warehouse::material.receipt.receipt', compact('receipt'));
    }

    public function storeConfirmReceipt(int|string $id, Request $request, BaseHttpResponse $response)
    {
        try {
            $receipt = MaterialReceiptConfirm::where(['id' => $id])->first();
            $requestData = $request->input();

            $totalQuantity = 0;
            foreach ($requestData['material'] as $key => $value) {
                $totalQuantity += $value['quantity'];
            }
            DB::beginTransaction();

            $receipt->update([
                'status' => MaterialProposalStatusEnum::APPOROVED,
                'invoice_confirm_name' => Auth::user()->name,
                'date_confirm' => Carbon::now()->format('Y-m-d'),
            ]);

            //Insert actual
            $dataInsertActual = [
                'receipt_id' => $id,
                'general_order_code' => $receipt->general_order_code,
                'warehouse_id' => $receipt->warehouse_id,
                'warehouse_name' => $receipt->warehouse_name,
                'warehouse_address' => $receipt->warehouse_address,
                'invoice_confirm_name' => Auth::user()->name,
                'quantity' => $totalQuantity,
                'status' => 'approved'
            ];

            try {
                $actual = ActualReceipt::query()->create($dataInsertActual);
                // //Create event for admin
                $arrNoti = [
                    'action' => 'xác nhận',
                    'permission' => "material-proposal-purchase.receipt",
                    'route' => route('material-receipt-confirm.view', $receipt->id),
                    'status' => 'Đã nhập kho'
                ];
                send_notify_cms_and_tele($actual, $arrNoti);
            } catch (Exception $err) {
                DB::rollback();
                throw new Exception($err->getMessage(), 1);
            }

            $lastBatchId = !empty(MaterialBatch::orderByDesc('id')->first()) ? MaterialBatch::orderByDesc('id')->first()->id + 1 : 1;
            $COUNT_CODE = 7;

            foreach ($receipt->receiptDetail->all() as $key => $value) {
                $materialId = $value->material_id;

                if (empty($materialId)) {
                    $materialId = Material::where('code', $value->material_code)->first()->id;
                }

                $dataInsertActualDetail = [
                    'actual_id' => $actual->id,
                    'material_id' => $materialId,
                    'material_code' => $value->material_code,
                    'material_name' => $value->material_name,
                    'material_unit' => $value->material_unit,
                    'material_quantity' => $requestData['material'][$value->id]['quantity'],
                    'material_price' => $value->material_price,
                    'reasoon' => null
                ];

                $newArr = [];

                if ($requestData['material'][$value->id]['quantity'] != $requestData['material'][$value->id]['quantity_default']) {
                    $newArr = [
                        'reasoon' => $requestData['material'][$value->id]['reasoon']
                    ];
                }

                $lastData = array_merge($dataInsertActualDetail, $newArr);
                try {
                    ActualReceiptDetail::query()->create($lastData);
                } catch (Exception $err) {
                    DB::rollBack();
                }

                $stockBy = QuantityMaterialStock::where(['warehouse_id' => $receipt->warehouse_id, 'material_id' => $materialId])->first();

                if (!empty($stockBy)) {
                    $qty = (int) $stockBy->quantity + (int) $requestData['material'][$value->id]['quantity'];

                    try {
                        $stockBy->update(['quantity' => $qty]);

                        event(new UpdatedContentEvent(MATERIAL_RECEIPT_PURCHASE_MODULE_SCREEN_NAME, $request, $stockBy));
                    } catch (Exception $err) {
                        DB::rollBack();
                        throw new Exception($err->getMessage(), 1);
                    }
                } else {
                    $dataInsert = [
                        'warehouse_id' => $receipt->warehouse_id,
                        'material_id' => $materialId,
                        'quantity' => (int) $requestData['material'][$value->id]['quantity'],
                    ];

                    try {
                        $stock = QuantityMaterialStock::create($dataInsert);
                        event(new CreatedContentEvent(MATERIAL_RECEIPT_PURCHASE_MODULE_SCREEN_NAME, $request, $stock));
                    } catch (Exception $err) {
                        DB::rollBack();
                        throw new Exception($err->getMessage(), 1);
                    }
                }

                $batch_code = str_pad($lastBatchId, $COUNT_CODE, '0', STR_PAD_LEFT);
                $prefix = "BAT";
                $qrCodeWithLogo = QrCode::size(150)->format('png')->merge('images/logo-handee.png', 0.3, true)->errorCorrection('H')->generate($prefix . $batch_code);

                $dataBatch = [
                    'stock_id' => $receipt->warehouse_id,
                    'receipt_id' => $id,
                    'batch_code' => $prefix . $batch_code,
                    'material_code' => $value->material_code,
                    'material_id' => $materialId,
                    'is_order_goods' => $receipt->is_purchase_goods,
                    'quantity' => (int) $requestData['material'][$value->id]['quantity'],
                    'start_qty' => (int) $requestData['material'][$value->id]['quantity'],
                    'qr_code_base64' => base64_encode($qrCodeWithLogo),
                ];
                $batchs = MaterialBatch::create($dataBatch);
                $lastBatchId++;
            }
        } catch (Exception $err) {
            DB::rollBack();
            throw new Exception($err->getMessage(), 1);
        }
        //DB commit
        DB::commit();

        return $response
            ->setPreviousUrl(route('material-receipt-confirm.index'))
            ->setNextUrl(route('material-receipt-confirm.index'))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }


    public function viewReceiptConfirmById(int|string $id, BaseHttpResponse $response)
    {
        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css']);

        $receipt = MaterialReceiptConfirm::where(['id' => $id])->with('receiptDetail')->first();

        $actual = ActualReceipt::where(['receipt_id' => $id])->with('autualDetail')->first();

        $this->pageTitle("Thông tin phiếu nhập kho nguyên phụ liệu");

        return view('plugins/warehouse::material.receipt.view', compact('receipt', 'actual'));
    }

    public function printQRCode(int|string $id, Request $request)
    {
        try {
            $currentUser = $request->user();
            $receipt = MaterialReceiptConfirm::where(['id' => $id])->first();
            if (!isset($receipt)) {
                throw new Exception("Không tìm thấy dữ liệu !", 'warehouse/material:(printQRCode)');
            }
            $checkPermission = !$currentUser->hasPermission('material-receipt-confirm.printQrCode') && !$receipt->status == MaterialProposalStatusEnum::APPOROVED;
            if ($checkPermission) {
                throw new Exception('Bạn không có quyền truy cập !', 'warehouse/material:(printQRCode)');
            }

            $batchCodeList = MaterialBatch::where(['receipt_id' => $id])->get();
            return view('plugins/warehouse::print-qr-code-theme.material-receipt', compact('receipt', 'batchCodeList'));
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }
}
