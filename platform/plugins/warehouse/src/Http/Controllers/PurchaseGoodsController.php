<?php

namespace Botble\Warehouse\Http\Controllers;

use ArchiElite\EcommerceNotification\Supports\EcommerceNotification;
use ArchiElite\NotificationPlus\Facades\NotificationPlus;
use Botble\Base\Events\AdminNotificationEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Models\AdminNotification;
use Botble\Base\Facades\PageTitle;
use Botble\Warehouse\Models\ReceiptInventory;
use Illuminate\Http\Request;
use Exception;
use Botble\Warehouse\Tables\ProposalPurchaseGoodsTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Warehouse\Forms\MaterialProposalPurchaseForm;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Supports\AdminNotificationItem;
use Botble\Warehouse\Enums\MaterialProposalStatusEnum;
use Botble\Warehouse\Enums\PurchaseOrderStatusEnum;
use Botble\Warehouse\Forms\PurchaseGoodsForm;
use Botble\Warehouse\Models\Material;
use Botble\Warehouse\Models\MaterialProposalPurchase;
use Botble\Warehouse\Models\MaterialProposalPurchaseDetail;
use Botble\Warehouse\Models\MaterialReceiptConfirm;
use Botble\Warehouse\Models\MaterialWarehouse;
use Botble\Warehouse\Models\ProposalPurchaseGoods;
use Botble\Warehouse\Models\ProposalPurchaseGoodsDetail;
use Botble\Warehouse\Models\ReceiptPurchaseGoods;
use Botble\Warehouse\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseGoodsController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this
            ->breadcrumb()
            ->add(trans('Đề xuất mua hàng'), route('proposal-purchase-goods.index'));
    }
    public function index(ProposalPurchaseGoodsTable $table)
    {

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        //Call file js
        Assets::addScriptsDirectly(
            [
                'vendor/core/plugins/warehouse/js/purchase-goods.js',
            ]
        )->addStylesDirectly([
            'vendor/core/plugins/warehouse/css/InOutMaterial.css',
        ]);

        $this->pageTitle('Tạo đơn đề xuất mua hàng');

        return $formBuilder->create(PurchaseGoodsForm::class, ['id' => 'botble-warehouse-forms-purchase-goods-form'])->renderForm();
    }

    public function store(Request $request, BaseHttpResponse $response)
    {
        $requestData = $request->input();

        // validate incoming request
        $validator = Validator::make($request->all(), [
            'material.*.quantity' => 'required|int|min:1',
            'material.*.supplier_id' => 'required',
            'warehouse_id' => 'required|int',
        ]);
        if ($validator->fails()) {
            return $response
                ->setError()
                ->setMessage('Vui lòng nhập đầy đủ thông tin!!');
        }

        $totalAmount = 0;
        $totalQuantity = 0;
        foreach ($requestData['material'] as $key => $material) {
            $totalAmount += ($material['quantity'] * $material['price']);
            $totalQuantity += ((int) $material['quantity']);
        }

        $materialWarehouse = MaterialWarehouse::where(['id' => $requestData['warehouse_id']])->first();

        $lastProposalId = ProposalPurchaseGoods::orderByDesc('id')->first();
        $preProposal = "PMH";

        $expectedDate = date('Y-m-d', strtotime($requestData['expected_date']));

        //Lấy số thứ tự mã phiếu nhập kho
        if(empty($lastProposalId)){
            $lastProductProposal = 1;
            $proposal_code = str_pad($lastProductProposal, 7, '0', STR_PAD_LEFT);
        }else{
            $productProposalCode = (int) substr($lastProposalId->code, 3);
            $proposal_code = str_pad($productProposalCode+1, 7, '0', STR_PAD_LEFT);
        }

        $proposal_code_last = $preProposal . $proposal_code;

        $dataPurchase = [
            'warehouse_id' => $requestData['warehouse_id'],
            'invoice_issuer_name' => Auth::user()->name,
            'general_order_code' => $requestData['general_order_code'] ?? '',
            'code' => $proposal_code_last,
            'warehouse_name' => $materialWarehouse->name,
            'warehouse_address' => $materialWarehouse->address,
            'quantity' => $totalQuantity,
            'total_amount' => (int) $totalAmount * 1,
            'title' => $requestData['title'],
            'description' => $requestData['description'],
            'expected_date' => $expectedDate,
        ];

        DB::beginTransaction();

        try {
            $materialProposal = ProposalPurchaseGoods::create($dataPurchase);
            event(new CreatedContentEvent(PROPOSAL_PURCHASE_GOODS_MODULE_SCREEN_NAME, $request, $materialProposal));

            //Create event for admin
            $arrNoti = [
                'action' => 'tạo',
                'permission' => "proposal-purchase-goods.create",
                'route' => route('proposal-purchase-goods.receipt', $materialProposal->id),
                'status' => 'Chờ duyệt'
            ];
            send_notify_cms_and_tele($materialProposal, $arrNoti);

            $proposalId = $materialProposal->id;

            foreach ($requestData['material'] as $key => $material) {
                if (gettype($key) == 'string') {
                    $oldMaterial = Material::where(['id' => $material['material_id']])->first();
                }
                $dataInsert = [
                    'proposal_id' => $proposalId,
                    'supplier_id' => $material['supplier_id'],
                    'supplier_name' => Supplier::where(['id' => $material['supplier_id']])->first()->name,
                    'material_code' => $material['code'] ?? '',
                    'material_name' => isset($oldMaterial) ? $oldMaterial->name : $material['name'],
                    'material_unit' => $material['unit'] ?? '',
                    'material_quantity' => $material['quantity'],
                    'material_price' => isset($oldMaterial) ? $oldMaterial->price : $material['price'],
                    'material_id' => gettype($key) == 'string' ? $material['material_id'] : null,
                ];

                try {
                    ProposalPurchaseGoodsDetail::create($dataInsert);
                } catch (Exception $err) {
                    DB::rollBack();
                    throw new Exception($err->getMessage(), 1);
                }
            }

        } catch (Exception $err) {
            DB::rollBack();
            throw new Exception($err->getMessage(), 1);
        }

        //DB commit
        DB::commit();

        return $response
            ->setPreviousUrl(route('proposal-purchase-goods.index'))
            ->setNextUrl(route('proposal-purchase-goods.index'))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function destroy(ProposalPurchaseGoods $purchaseGoods, Request $request, BaseHttpResponse $response)
    {
        try {
            abort_if($purchaseGoods->status == PurchaseOrderStatusEnum::APPOROVED, 403);

            $purchaseGoods->delete();

            event(new DeletedContentEvent(MATERIAL_PROPOSAL_PURCHASE_MODULE_SCREEN_NAME, $request, $purchaseGoods));

            //Create event for admin
            event(new AdminNotificationEvent(
                AdminNotificationItem::make()
                    ->title("Xoá đơn đề xuất mua hàng")
                    ->description($purchaseGoods->description ?? '')
                    ->action(trans('plugins/ecommerce::order.new_order_notifications.view'), route('proposal-purchase-goods.index'))
                    ->permission('proposal-purchase-goods.index')
            ));
            //Create b
            if (in_array(Telegram::class, NotificationPlus::getAvailableDrivers())) {
                $user = request()->user();
                EcommerceNotification::make()
                    ->sendNotifyToDriversUsing('', '{{user_role_name}} - {{ user_name }} đã {{ action }} {{  warehouse_name }}.', [
                        'title' => $purchaseGoods->title,
                        'proposal_id' => $purchaseGoods->code,
                        'proposal_url' => route('proposal-purchase-goods.index'),
                        'proposal' => $purchaseGoods,
                        'status' => 'Đã xoá',
                        'expected_date' => $purchaseGoods->expected_date,
                        'user_name' => $user->name,
                        'user_role_name' => $user->roles()->first()?->name,
                        'warehouse_name' => $purchaseGoods->warehouse_name,
                        'action' => ' xoá đơn đề xuất mua hàng ',
                    ]);
            }

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function receiptProposal(int|string $id)
    {
        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/ecommerce/js/order.js',
                'https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js',
                'https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js',
            ])
            ->addScripts(['blockui', 'input-mask']);

        $proposal = ProposalPurchaseGoods::where(['id' => $id])->with('proposalDetail')->first();

        abort_if($proposal->status == PurchaseOrderStatusEnum::APPOROVED, 403);

        $this->pageTitle(__('Duyệt đơn đề xuất nhập kho'));

        return view('plugins/warehouse::purchase-goods.proposal.receipt', compact('proposal'));
    }

    public function viewProposalByCode(int|string $id, BaseHttpResponse $response)
    {
        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css']);

        $proposal = ProposalPurchaseGoods::where(['id' => $id])->with('proposalDetail')->first();
        $receipt = ReceiptPurchaseGoods::where(['proposal_id' => $id])->with('receiptDetail')->first();

        $this->pageTitle('Thông tin chi tiết đơn đề xuất mua hàng');

        return view('plugins/warehouse::purchase-goods.proposal.view', compact('proposal', 'receipt'));
    }

    public function createPurchaseMaterial(FormBuilder $formBuilder)
    {
        //Call file js
        Assets::addScriptsDirectly(
            [
                'vendor/core/plugins/warehouse/js/InOutMaterial.js',
            ]
        );

        $this->pageTitle(__('Phiếu đề xuất mua hàng'));

        return $formBuilder->create(MaterialProposalPurchaseForm::class)->renderForm();
    }

    public function cancelProposalPurchaseGoods(int|string $id, Request $request, BaseHttpResponse $response)
    {
        $proposal = ProposalPurchaseGoods::where(['id' => $id])->first();

        if (empty($proposal)) {
            return $response->setError()->setMessage('Không tìm thấy đơn đề xuất này!!');
        }

        if ($proposal->status == PurchaseOrderStatusEnum::APPOROVED) {
            return $response->setError()->setMessage('Đơn đề xuất này đã được duyệt, không thể huỷ đơn!!');
        }

        $proposal->update([
            'reasoon_cancel' => $request->input()['reasoon'],
            'status' => PurchaseOrderStatusEnum::DENIED,
            'invoice_confirm_name' => Auth::user()->name,
            'date_confirm' => Carbon::now()->format('Y-m-d')
        ]);

        //Thông báo hủy đơn cho admin
        $arrNoti = [
            'action' => 'hủy',
            'permission' => "receipt-purchase-goods.receipt",
            'route' => route('proposal-purchase-goods.view.code', $proposal->id),
            'status' => 'Đã huỷ đơn'
        ];
        send_notify_cms_and_tele($proposal, $arrNoti);

        //Thông báo hủy đơn cho người tạo đơn
        $arrNotiCreator = [
            'action' => 'hủy',
            'permission' => "receipt-purchase-goods.create",
            'route' => route('proposal-purchase-goods.view.code', $proposal->id),
            'status' => 'Đã huỷ đơn'
        ];
        send_notify_cms_and_tele($proposal, $arrNotiCreator);

        return $response
            ->setPreviousUrl(route('proposal-purchase-goods.index'))
            ->setNextUrl(route('proposal-purchase-goods.index'))
            ->setMessage("Huỷ đơn đề xuất thành công!!");
    }

    public function edit(ProposalPurchaseGoods $purchaseGoods, FormBuilder $formBuilder)
    {
        abort_if($purchaseGoods->status == PurchaseOrderStatusEnum::APPOROVED, 403);
        //Call file js
        Assets::addScriptsDirectly(
            [
                'vendor/core/plugins/warehouse/js/purchase-goods.js',
            ]
        );
        $this->pageTitle(trans('Chỉnh sửa đơn đề xuất'));

        return $formBuilder->create(PurchaseGoodsForm::class, ['model' => $purchaseGoods, 'id' => 'botble-warehouse-forms-purchase-goods-form'])->renderForm();
    }

    public function update(
        ProposalPurchaseGoods $purchaseGoods,
        Request $request,
        BaseHttpResponse $response,
    ) {
        if (empty($purchaseGoods)) {
            return $response
                ->setPreviousUrl(route('proposal-purchase-goods.index'))
                ->setNextUrl(route('proposal-purchase-goods.index'))
                ->setError()
                ->setMessage("Không tìm thấy đơn đề xuất này!!");
        }

        abort_if($purchaseGoods->status == PurchaseOrderStatusEnum::APPOROVED, 403); //Check status of current proposal

        //Check validate data
        $requestData = $request->input();

        $validator = Validator::make($request->all(), [
            'material.*.quantity' => 'required|int|min:1',
            'material.*.supplier_id' => 'required',
            'warehouse_id' => 'required|int',
        ]);

        if ($validator->fails()) {
            return $response
                ->setError()
                ->setMessage('Vui lòng nhập đầy đủ thông tin!!');
        }

        $totalAmount = 0;
        $totalQuantity = 0;
        foreach ($requestData['material'] as $key => $material) {
            $price = $material['price'] ?? 0;
            $totalAmount += ($material['quantity'] * $price);
            $totalQuantity += ((int) $material['quantity']);
        }

        $materialWarehouse = MaterialWarehouse::where(['id' => $requestData['warehouse_id']])->first();

        $expectedDate = date('Y-m-d', strtotime($requestData['expected_date']));

        $dataPurchaseGoods = [
            'warehouse_id' => $requestData['warehouse_id'],
            'invoice_issuer_name' => Auth::user()->name,
            'general_order_code' => $requestData['general_order_code'] ?? '',
            'code' => $purchaseGoods->code,
            'warehouse_name' => $materialWarehouse->name,
            'warehouse_address' => $materialWarehouse->address,
            'quantity' => $totalQuantity,
            'total_amount' => (int) $totalAmount * 1,
            'title' => $requestData['title'],
            'description' => $requestData['description'],
            'expected_date' => $expectedDate,
            'status' => PurchaseOrderStatusEnum::PENDING,
            'reasoon_cancel' => null,
        ];

        DB::beginTransaction();

        try {
            $purchaseGoods->update($dataPurchaseGoods);
            event(new UpdatedContentEvent(PROPOSAL_PURCHASE_GOODS_MODULE_SCREEN_NAME, $request, $purchaseGoods));

            $arrNoti = [
                'action' => 'chỉnh sửa',
                'permission' => "receipt-purchase-goods.receipt",
                'route' => route('proposal-purchase-goods.edit', $purchaseGoods->id),
                'status' => 'Chờ duyệt'
            ];
            send_notify_cms_and_tele($purchaseGoods, $arrNoti);

            //Delete proposal purchase detail
            foreach ($purchaseGoods->proposalDetail as $key => $proposalDetail) {
                $proposalDetail->delete();
            }

            foreach ($requestData['material'] as $key => $material) {
                $dataInsert = [
                    'proposal_id' => $purchaseGoods->id,
                    'supplier_id' => $material['supplier_id'],
                    'supplier_name' => Supplier::where(['id' => $material['supplier_id']])->first()->name,
                    'material_code' => $material['code'] ?? '',
                    'material_name' => $material['name'],
                    'material_unit' => $material['unit'] ?? '',
                    'material_quantity' => $material['quantity'],
                    'material_price' => $material['price'] ?? null,
                    'material_id' => gettype($key) == 'string' ? $material['material_id'] : null,
                ];

                try {
                    ProposalPurchaseGoodsDetail::create($dataInsert);
                } catch (Exception $err) {
                    DB::rollBack();
                    throw new Exception($err->getMessage(), 1);
                }
            }

        } catch (Exception $err) {
            DB::rollBack();
            throw new Exception($err->getMessage(), 1);
        }

        //DB commit
        DB::commit();

        return $response
            ->setPreviousUrl(route('proposal-purchase-goods.index'))
            ->setNextUrl(route('proposal-purchase-goods.index'))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }
}
