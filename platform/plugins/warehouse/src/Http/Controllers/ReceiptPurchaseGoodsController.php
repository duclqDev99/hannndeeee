<?php

namespace Botble\Warehouse\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Illuminate\Http\Request;
use Exception;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Warehouse\Enums\MaterialProposalStatusEnum;
use Botble\Warehouse\Enums\MaterialReceiptStatusEnum;
use Botble\Warehouse\Enums\PurchaseOrderStatusEnum;
use Botble\Warehouse\Models\Material;
use Botble\Warehouse\Models\MaterialReceiptConfirm;
use Botble\Warehouse\Models\MaterialReceiptConfirmDetail;
use Botble\Warehouse\Models\ProposalPurchaseGoods;
use Botble\Warehouse\Models\ReceiptPurchaseGoods;
use Botble\Warehouse\Models\ReceiptPurchaseGoodsDetail;
use Botble\Warehouse\Tables\ReceiptPurchaseGoodsTable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReceiptPurchaseGoodsController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this
            ->breadcrumb()
            ->add(trans('Phiếu mua hàng'), route('receipt-purchase-goods.index'));
    }
    public function index(ReceiptPurchaseGoodsTable $table)
    {
        Assets::addScripts(['bootstrap-editable', 'jquery-ui'])
        ->addStyles(['bootstrap-editable']);


        return $table->renderTable();
    }

    public function receiptProposal(int|string $id, Request $request, BaseHttpResponse $response)
    {
        $requestData = $request->input();

        $expectedDate = date('Y-m-d', strtotime($requestData['expected_date']));
        $currentDate = Carbon::now()->format('Y-m-d');
        if($expectedDate < $currentDate)
        {
            return $response
            ->setError()
            ->setMessage('Vui lòng nhập ngày dự kiến lớn hơn ngày hiện tại!');
        }

        foreach ($requestData['material'] as $key => $value) {
            if($value['material_id'] == null){
                if(!empty(Material::where('code', $value['material_code'])->first()))
                {
                    return $response
                    ->setError()
                    ->setMessage('Mã nguyên phụ liệu '. $value['material_code'] .' đã tồn tại!!');
                }
            }
        }

        DB::beginTransaction();

        $proposal = ProposalPurchaseGoods::where(['id' => $id])->first();

        if(empty($proposal))
        {
            return $response
            ->setPreviousUrl(route('material-proposal-purchase.index'))
            ->setNextUrl(route('material-proposal-purchase.index'))
            ->setError()
            ->setMessage('Không tìm thấy đơn đề xuất này!!');
        }

        $proposal->update([
            'status' => MaterialProposalStatusEnum::APPOROVED,
            'invoice_confirm_name' => Auth::user()->name,
            'date_confirm' => Carbon::now()->format('Y-m-d'),
        ]);

        $totalAmount = 0;
        $totalQuantity = 0;
        foreach ($requestData['material'] as $key => $material)
        {
            $totalAmount += ($material['quantity'] * $material['material_price']);
            $totalQuantity += ((int)$material['quantity']);
        }

        $dataReceipt = [
            'warehouse_id' => $proposal->warehouse_id,
            'invoice_issuer_name' => $proposal->invoice_issuer_name,
            'invoice_confirm_name' => Auth::user()->name,
            'general_order_code' => $proposal->general_order_code,
            'proposal_id' => $proposal->id,
            'warehouse_name' => $proposal->warehouse_name,
            'warehouse_address' => $proposal->warehouse_address,
            'quantity' => $totalQuantity,
            'total_amount' => (int) $totalAmount,
            'title' => $proposal->title,
            'description' => $proposal->description,
            'expected_date' => $proposal->expected_date,
            'status' => MaterialReceiptStatusEnum::PENDING,
            'date_confirm' => Carbon::now()->format('Y-m-d'),
        ];

        try{
            $materialReceipt = ReceiptPurchaseGoods::create($dataReceipt);
            event(new CreatedContentEvent(RECEIPT_PURCHASE_GOODS_MODULE_SCREEN_NAME, $request, $materialReceipt));

            //Create notify for creator proposal
            $arrNotiCreator = [
                'action' => 'duyệt',
                'permission' => "proposal-purchase-goods.create",
                'route' => route('proposal-purchase-goods.view.code', $proposal->id),
                'status' => 'Đã duyệt'
            ];
            send_notify_cms_and_tele($proposal, $arrNotiCreator);

            //Create notify for creator proposal
            $arrNoti = [
                'action' => 'tạo',
                'permission' => "receipt-purchase-goods.confirm",
                'route' => route('receipt-purchase-goods.confirm', $materialReceipt->id),
                'status' => 'Chờ mua hàng'
            ];
            send_notify_cms_and_tele($materialReceipt, $arrNoti);

        }catch(Exception $err)
        {
            DB::rollBack();
            throw new Exception($err->getMessage(), 1);
        }

        for ($i=0; $i < count($requestData['material']); $i++) {
            $dataInsert = [
                'receipt_id' => $materialReceipt->id,
                'supplier_id' => $proposal->proposalDetail[$i]->supplier_id,
                'supplier_name' => $proposal->proposalDetail[$i]->supplier_name,
                'material_code' => $requestData['material'][$proposal->proposalDetail[$i]->id]['material_code'],
                'material_name' => $proposal->proposalDetail[$i]->material_name,
                'material_unit' => $requestData['material'][$proposal->proposalDetail[$i]->id]['material_unit'],
                'material_quantity' => $requestData['material'][$proposal->proposalDetail[$i]->id]['quantity'],
                'material_price' => $requestData['material'][$proposal->proposalDetail[$i]->id]['material_price'],
            ];

            try{
                ReceiptPurchaseGoodsDetail::create($dataInsert);

                if($requestData['material'][$proposal->proposalDetail[$i]->id]['material_id'] == null){
                    $newMaterial = Material::query()->create([
                        'name' => $proposal->proposalDetail[$i]->material_name,
                        'unit' => $requestData['material'][$proposal->proposalDetail[$i]->id]['material_unit'],
                        'code' => $requestData['material'][$proposal->proposalDetail[$i]->id]['material_code'],
                        'price' => (int)$requestData['material'][$proposal->proposalDetail[$i]->id]['material_price'],
                        'min' => 0,
                    ]);
                }

            }catch(Exception $err){
                DB::rollBack();
                throw new Exception($err->getMessage(), 1);
            }
        }

        //DB commit
        DB::commit();

        return $response
            ->setPreviousUrl(route('proposal-purchase-goods.index'))
            ->setNextUrl(route('proposal-purchase-goods.index'))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function confirmReceipt(int|string $id)
    {
        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/ecommerce/js/order.js',
                'vendor/core/plugins/warehouse/js/receipt.js',
            ])
            ->addScripts(['blockui', 'input-mask']);

        $receipt = ReceiptPurchaseGoods::where(['id' => $id])->with('receiptDetail')->first();

        abort_if($receipt->status == PurchaseOrderStatusEnum::APPOROVED, 403);

        $this->pageTitle(__('Xác nhận mua hàng'));

        return view('plugins/warehouse::purchase-goods.receipt.receipt', compact('receipt'));
    }

    public function storeConfirmReceipt(int|string $id, Request $request, BaseHttpResponse $response)
    {
        $receipt = ReceiptPurchaseGoods::where(['id' => $id])->first();

        abort_if($receipt->status == PurchaseOrderStatusEnum::APPOROVED, 403);

        $receipt->update([
            'status' => MaterialProposalStatusEnum::APPOROVED,
            'invoice_confirm_name' => Auth::user()->name,
            'date_confirm' => Carbon::now()->format('Y-m-d'),
        ]);

        //Create proposal purchase material with status is approved
        $dataInsertProposal = [
            'general_order_code' => $receipt->general_order_code,
            'warehouse_id' => $receipt->warehouse_id,
            'invoice_issuer_name' => Auth::user()->name,
            'invoice_confirm_name' => $receipt->invoice_confirm_name,
            'proposal_id' => $receipt->id,
            'warehouse_name' => $receipt->warehouse_name,
            'warehouse_address' => $receipt->warehouse_address,
            'is_from_supplier' => true,
            'quantity' => $receipt->quantity,
            'total_amount' => $receipt->total_amount,
            'title' => $receipt->title,
            'description' => $receipt->description,
            'expected_date' => $receipt->expected_date,
            'is_purchase_goods' => true
        ];

        // abort_if($receipt[0]->inventory_id !== $inventoryId, 403);
        DB::beginTransaction();

        $receiptProposalMaterial = MaterialReceiptConfirm::query()->create($dataInsertProposal);

        foreach ($receipt->receiptDetail->all() as $key => $value) {

            try{
                //Create proposal purchase material detail
                $dataInsertDetail = [
                    'receipt_id' => $receiptProposalMaterial->id,
                    'supplier_name' => $value->supplier_name,
                    'supplier_id' => $value->supplier_id,
                    'material_code' => $value->material_code,
                    'material_name' => $value->material_name,
                    'material_unit' => $value->material_unit,
                    'material_quantity' => $value->material_quantity,
                    'material_price' => $value->material_price,
                ];
                MaterialReceiptConfirmDetail::create($dataInsertDetail);

            }catch(Exception $err){
                DB::rollBack();
                throw new Exception($err->getMessage(), 1);
            }
        }
        //DB commit
        DB::commit();

        return $response
            ->setPreviousUrl(route('receipt-purchase-goods.index'))
            ->setNextUrl(route('receipt-purchase-goods.index'))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function viewDetailReceiptGoods(int|string $id, BaseHttpResponse $response)
    {
        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css']);

        $receipt = ReceiptPurchaseGoods::where(['id' => $id])->with('receiptDetail')->first();

        $this->pageTitle("Thông tin phiếu mua hàng");

        return view('plugins/warehouse::purchase-goods.receipt.view', compact('receipt'));
    }

    public function getStatusJson(): array
    {
        $pl = [
            [
                'value' => PurchaseOrderStatusEnum::PENDING,
                'text' => 'Chờ mua hàng',
            ],
            [
                'value' => PurchaseOrderStatusEnum::APPOROVED,
                'text' => 'Đã mua hàng',
            ],
        ];

        return $pl;
    }

    public function postStatusOrder(Request $request, BaseHttpResponse $response)
    {
        DB::beginTransaction();
        try{
            $materialReceipt = ReceiptPurchaseGoods::where('id', $request->pk)->first();

            if(isset($materialReceipt))
            {
                if($materialReceipt->status == PurchaseOrderStatusEnum::APPOROVED){
                    return $response
                    ->setError()
                    ->setMessage('Phiếu mua hàng đã được xác nhận!!');
                }
                $materialReceipt->update(['status' => $request->value]);
                //Create proposal purchase material with status is approved
                $dataInsertReceipt = [
                    'general_order_code' => $materialReceipt->general_order_code,
                    'warehouse_id' => $materialReceipt->warehouse_id,
                    'invoice_issuer_name' => Auth::user()->name,
                    'invoice_confirm_name' => $materialReceipt->invoice_confirm_name,
                    'proposal_id' => $materialReceipt->id,
                    'warehouse_name' => $materialReceipt->warehouse_name,
                    'warehouse_address' => $materialReceipt->warehouse_address,
                    'is_from_supplier' => true,
                    'quantity' => $materialReceipt->quantity,
                    'total_amount' => $materialReceipt->total_amount,
                    'title' => $materialReceipt->title,
                    'description' => $materialReceipt->description,
                    'expected_date' => $materialReceipt->expected_date,
                    'is_purchase_goods' => true
                ];

                $receiptMaterial = MaterialReceiptConfirm::query()->create($dataInsertReceipt);

                // dd($receiptMaterial);
                foreach ($materialReceipt->receiptDetail->all() as $key => $value) {
                    //Create proposal purchase material detail
                    $dataInsertDetail = [
                        'receipt_id' => $receiptMaterial->id,
                        'supplier_name' => $value->supplier_name,
                        'supplier_id' => $value->supplier_id,
                        'material_code' => $value->material_code,
                        'material_name' => $value->material_name,
                        'material_unit' => $value->material_unit,
                        'material_quantity' => $value->material_quantity,
                        'material_price' => $value->material_price,
                        'material_id' => Material::where('code', $value->material_code)->first()->id ?: '',
                    ];
                    MaterialReceiptConfirmDetail::create($dataInsertDetail);
                }

                //Create event for admin
                $arrNoti = [
                    'action' => 'xác nhận',
                    'permission' => "receipt-purchase-goods.receipt",
                    'route' => route('receipt-purchase-goods.view', $materialReceipt->id),
                    'status' => 'Chờ nhập kho'
                ];
                send_notify_cms_and_tele($materialReceipt, $arrNoti);

                //Create event for stock
                $arrNotiStock = [
                    'action' => 'tạo',
                    'permission' => "material-receipt-confirm.confirm",
                    'route' => route('material-receipt-confirm.confirm', $receiptMaterial->id),
                    'status' => 'Chờ nhập kho'
                ];
                send_notify_cms_and_tele($receiptMaterial, $arrNotiStock);
            }

        }catch(Exception $err){
            DB::rollBack();
            throw new Exception($err->getMessage(), 1);
        }

        DB::commit();

        return $response
        ->setMessage(trans('core/base::notices.create_success_message'));
    }
}
