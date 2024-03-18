<?php

namespace Botble\Warehouse\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Illuminate\Http\Request;
use Exception;
use Botble\Warehouse\Tables\MaterialProposalPurchaseTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Warehouse\Forms\MaterialProposalPurchaseForm;
use Botble\Base\Forms\FormBuilder;
use Botble\Warehouse\Enums\MaterialProposalStatusEnum;
use Botble\Warehouse\Models\Material;
use Botble\Warehouse\Models\MaterialOut;
use Botble\Warehouse\Models\MaterialOutDeatail;
use Botble\Warehouse\Models\MaterialProposalPurchase;
use Botble\Warehouse\Models\MaterialProposalPurchaseDetail;
use Botble\Warehouse\Models\MaterialReceiptConfirm;
use Botble\Warehouse\Models\MaterialWarehouse;
use Botble\Warehouse\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MaterialProposalPurchaseController extends BaseController
{
    protected string $prefixCodeIssue = 'XK';
    protected string $prefixCodeReceipt = 'NK';

    public function __construct()
    {
        parent::__construct();
        $this
            ->breadcrumb()
            ->add(trans('Đề xuất nhập kho'), route('material-proposal-purchase.index'));
    }
    public function index(MaterialProposalPurchaseTable $table)
    {

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        //Call file js
        Assets::addScriptsDirectly(
            [
                'vendor/core/plugins/warehouse/js/InOutMaterial.js',
            ]
        )->addStylesDirectly([
            'vendor/core/plugins/warehouse/css/InOutMaterial.css',
        ]);

        $this->pageTitle('Tạo đơn đề xuất nhập kho');

        return $formBuilder->create(MaterialProposalPurchaseForm::class, ['id' => 'botble-warehouse-forms-material-proposal-purchase-form'])->renderForm();
    }

    public function store(Request $request, BaseHttpResponse $response)
    {
        $requestData = $request->input();

        $expectedDate = date('Y-m-d', strtotime($requestData['expected_date'] ?? Carbon::now()));

        $parameter = '';
        $wh_departure_id = null;
        $wh_departure_name = null;
        $is_from_supplier = true;

        if($request->input()['type_proposal'] == 'stock')
        {
            $parameter = 'stock';
            $is_from_supplier = false;
            $wh_departure_id = $requestData['detination_wh_id'];
            $wh_departure_name = MaterialWarehouse::where(['id' => $requestData['detination_wh_id']])->first()?->name;
        }else{//supplier
            $parameter = 'supplier';
            $is_from_supplier = true;
        }

        $prefixProposal = 'NK';
        $prefixIssue = 'XK';

        $totalAmount = 0;
        $totalQuantity = 0;
        foreach ($requestData["$parameter"]['material'] as $key => $material)
        {
            $materialModelPrice = Material::where(['code' => $material['code']])->first()?->price;
            $totalAmount += ($material['quantity'] * $materialModelPrice);
            $totalQuantity += ((int)$material['quantity']);
        }

        $materialWarehouse = MaterialWarehouse::where(['id' => $requestData['warehouse_id']])->first();

        $lastProposalId = MaterialProposalPurchase::orderByDesc('id')->first();

        $COUNT_CODE = 7;
        //Lấy số thứ tự mã phiếu nhập kho
        if(empty($lastProposalId)){
            $lastProductProposal = 1;
            $proposal_code = str_pad($lastProductProposal, 7, '0', STR_PAD_LEFT);
        }else{
            $productProposalCode = (int) substr($lastProposalId->proposal_code, 2);
            $proposal_code = str_pad($productProposalCode+1, 7, '0', STR_PAD_LEFT);
        }

        $proposal_code_last = $this->prefixCodeReceipt . $proposal_code;

        $dataPurchase = [
            'general_order_code' => $requestData['general_order_code'] ?? '',
            'warehouse_id' => $requestData['warehouse_id'],
            'invoice_issuer_name' => Auth::user()->name,
            'proposal_code' => $proposal_code_last,
            'wh_departure_id' => $wh_departure_id,
            'wh_departure_name' => $wh_departure_name,
            'is_from_supplier' => $is_from_supplier,
            'warehouse_name' => $materialWarehouse->name,
            'warehouse_address' => $materialWarehouse->address,
            'quantity' => $totalQuantity,
            'total_amount' => (int) $totalAmount,
            'title' => $requestData['title'],
            'description' => $requestData['description'],
            'expected_date' =>  $expectedDate,
        ];

        DB::beginTransaction();

        try{
            $materialProposal = MaterialProposalPurchase::create($dataPurchase);
            event(new CreatedContentEvent(MATERIAL_PROPOSAL_PURCHASE_MODULE_SCREEN_NAME, $request, $materialProposal));

            $arrNoti = [
                'action' => 'tạo',
                'permission' => "material-proposal-purchase.receipt",
                'route' => route('material-proposal-purchase.receipt', $materialProposal->id),
                'status' => 'Chờ duyệt'
            ];
            send_notify_cms_and_tele($materialProposal, $arrNoti);

            $lastIssueId = !empty(MaterialOut::orderByDesc('id')->first()) ? MaterialOut::orderByDesc('id')->first()->id + 1 : 1;

            $issue_code = str_pad($lastIssueId, $COUNT_CODE, '0', STR_PAD_LEFT);

            if($parameter == 'stock')
            {
                $lastProposalId = MaterialOut::orderByDesc('id')->first();

                $COUNT_CODE = 7;
                //Lấy số thứ tự mã phiếu nhập kho
                if(empty($lastProposalId)){
                    $lastProductProposal = 1;
                    $proposal_code = str_pad($lastProductProposal, 7, '0', STR_PAD_LEFT);
                }else{
                    $productProposalCode = (int) substr($lastProposalId->proposal_code, 2);
                    $proposal_code = str_pad($productProposalCode+1, 7, '0', STR_PAD_LEFT);
                }

                $proposal_code_last = $this->prefixCodeIssue . $proposal_code;
                $dataMaterialOut = [
                    'general_order_code' => $requestData['general_order_code'] ?? '',
                    'warehouse_id' => $wh_departure_id,
                    'warehouse_type' => MaterialWarehouse::class,
                    'warehouse_out_id' => $requestData['warehouse_id'],
                    'invoice_issuer_name' => Auth::user()->name,
                    'proposal_code' => $proposal_code_last,
                    'warehouse_name' => $wh_departure_name,
                    'warehouse_address' => $materialWarehouse->address,
                    'quantity' => $totalQuantity,
                    'total_amount' => (int) $totalAmount,
                    'title' => "Xuất hàng đến kho: " . $materialWarehouse->name,
                    'description' => null,
                    'expected_date' => $expectedDate,
                    'proposal_purchase_id' => $materialProposal->id,
                    'issuer_id' => Auth::user()->id,
                ];

                $materialOut = MaterialOut::query()->create($dataMaterialOut);

                $arrNotiOut = [
                    'action' => 'tạo',
                    'permission' => "proposal-goods-issue.create",
                    'route' => route('proposal-goods-issue.list.out', $materialOut->id),
                    'status' => 'Chờ duyệt'
                ];
                send_notify_cms_and_tele($materialOut, $arrNotiOut);
            }

            $proposalId = $materialProposal->id;

            foreach ($requestData["$parameter"]['material'] as $key => $material) {
                $supplier_id = null;
                $supplier_name = null;

                if($parameter == 'supplier')
                {
                    $supplier_id = $requestData['supplier_id'];
                    $supplier_name = Supplier::where(['id' => $requestData['supplier_id']])->first()?->name;
                }

                $materialByCode = Material::where(['code' => $material['code']])->first();

                $dataInsert = [
                    'proposal_id' => $proposalId,
                    'supplier_id' => $supplier_id,
                    'supplier_name' => $supplier_name,
                    'material_code' => $material['code'],
                    'material_name' => !isset($material['name']) ? Material::where(['code' => $material['code']])->first()?->name : $material['name'],
                    'material_unit' => $material['unit'],
                    'material_quantity' => (int) $material['quantity'],
                    'material_price' => (int) $materialByCode->price,
                    'material_id' => !empty($material['material_id']) ? $material['material_id'] : $materialByCode->id,
                ];

                try{
                    MaterialProposalPurchaseDetail::create($dataInsert);

                    if(isset($materialOut))
                    {
                        $dataOutDetail = [
                            'proposal_id' => $materialOut->id,
                            'material_code' => $material['code'],
                            'material_name' => !isset($material['name']) ? Material::where(['code' => $material['code']])->first()?->name : $material['name'],
                            'material_unit' => $material['unit'],
                            'material_quantity' => $material['quantity'],
                            'material_price' => Material::where(['code' => $material['code']])->first()?->price,
                        ];
                        MaterialOutDeatail::create($dataOutDetail);
                    }
                }catch(Exception $err){
                    DB::rollBack();
                    throw new Exception($err->getMessage(), 1);
                }
            }

        }catch(Exception $err)
        {
            DB::rollBack();
            throw new Exception($err->getMessage(), 1);
        }

        //DB commit
        DB::commit();

        return $response
            ->setPreviousUrl(route('material-proposal-purchase.index'))
            ->setNextUrl(route('material-proposal-purchase.edit', $materialProposal->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(MaterialProposalPurchase $proposal, FormBuilder $formBuilder)
    {
        //nếu đơn đã duyệt hoặc được tạo từ đơn xuất kho khác thì xuất trang 403
        abort_if($proposal->status == MaterialProposalStatusEnum::APPOROVED || !empty($proposal->proposal_out_id), 403);
        //Call file js
        Assets::addScriptsDirectly(
            [
                'vendor/core/plugins/warehouse/js/InOutMaterial.js',
            ]
        )->addStylesDirectly([
            'vendor/core/plugins/warehouse/css/InOutMaterial.css',
        ]);
        $this->pageTitle(trans('Chỉnh sửa đơn đề xuất'));

        return $formBuilder->create(MaterialProposalPurchaseForm::class, ['model' => $proposal, 'id' => 'botble-warehouse-forms-material-proposal-purchase-form'])->renderForm();
    }

    public function update(
        MaterialProposalPurchase $proposal,
        Request $request,
        BaseHttpResponse $response,
    )
    {
        if(empty($proposal)){
            return $response
            ->setPreviousUrl(route('material-proposal-purchase.index'))
            ->setNextUrl(route('material-proposal-purchase.index'))
            ->setError()
            ->setMessage("Không tìm thấy đơn đề xuất này!!");
        }

        //nếu đơn đã duyệt hoặc được tạo từ đơn xuất kho khác thì xuất trang 403
        abort_if($proposal->status == MaterialProposalStatusEnum::APPOROVED || !empty($proposal->proposal_out_id), 403);

        //Check validate data
        $requestData = $request->input();

        $validator = [
            'material.*.quantity' => 'required|int|min:1',
        ];

        $parameter = '';
        $wh_departure_id = null;
        $wh_departure_name = null;
        $is_from_supplier = true;

        if($request->input()['type_proposal'] == 'stock')
        {
            $parameter = 'stock';
            $is_from_supplier = false;
            $wh_departure_id = $requestData['detination_wh_id'];
            $wh_departure_name = MaterialWarehouse::where(['id' => $requestData['detination_wh_id']])->first()?->name;
            $wh_departure_address = MaterialWarehouse::where(['id' => $requestData['detination_wh_id']])->first()?->address;
        }else{//supplier
            $parameter = 'supplier';
            $is_from_supplier = true;
        }
        $validator = Validator::make($request->input()["$parameter"], $validator);

        if ($validator->fails()) {
            return $response
            ->setError()
            ->setMessage('Vui lòng nhập đầy đủ thông tin!!');
        }

        $totalAmount = 0;
        $totalQuantity = 0;
        foreach ($requestData["$parameter"]['material'] as $key => $material)
        {
            $materialModelPrice = Material::where(['code' => $material['code']])->first()?->price;
            $totalAmount += ($material['quantity'] * $materialModelPrice);
            $totalQuantity += ((int)$material['quantity']);
        }

        $materialWarehouse = MaterialWarehouse::where(['id' => $requestData['warehouse_id']])->first();

        $expectedDate = date('Y-m-d', strtotime($requestData['expected_date']));

        $dataPurchaseUpdate = [
            'general_order_code' => $requestData['general_order_code'] ?? '',
            'warehouse_id' => $requestData['warehouse_id'],
            'invoice_issuer_name' => Auth::user()->name,
            'proposal_code' => $proposal->proposal_code,
            'wh_departure_id' => $wh_departure_id,
            'wh_departure_name' => $wh_departure_name,
            'is_from_supplier' => $is_from_supplier,
            'warehouse_name' => $materialWarehouse->name,
            'warehouse_address' => $materialWarehouse->address,
            'quantity' => $totalQuantity,
            'total_amount' => (int) $totalAmount,
            'title' => $requestData['title'],
            'description' => $requestData['description'],
            'expected_date' => $expectedDate,
            'status' => MaterialProposalStatusEnum::PENDING,
            'reasoon_cancel' => null,
        ];

        DB::beginTransaction();

        try{
            $proposal->update($dataPurchaseUpdate);
            event(new UpdatedContentEvent(MATERIAL_PROPOSAL_PURCHASE_MODULE_SCREEN_NAME, $request, $proposal));

            $arrNoti = [
                'action' => 'cập nhật',
                'permission' => "material-proposal-purchase.receipt",
                'route' => route('material-proposal-purchase.edit', $proposal->id),
                'status' => 'Chờ duyệt'
            ];
            send_notify_cms_and_tele($proposal, $arrNoti);

            //Get material out by id proposal
            $materialOutByProposal = MaterialOut::where(['proposal_purchase_id' => $proposal->id])->first();
            if($parameter == 'stock')
            {
                if(!empty($materialOutByProposal))
                {
                    $dataMaterialOut = [
                        'general_order_code' => $requestData['general_order_code'] ?? '',
                        'warehouse_id' => $wh_departure_id,
                        'warehouse_type' => MaterialWarehouse::class,
                        'warehouse_out_id' => $requestData['warehouse_id'],
                        'invoice_issuer_name' => Auth::user()->name,
                        'proposal_code' => $materialOutByProposal->proposal_code,
                        'warehouse_name' => $wh_departure_name,
                        'warehouse_address' => $wh_departure_address,
                        'quantity' => $totalQuantity,
                        'total_amount' => (int) $totalAmount,
                        'title' => "Xuất hàng đến kho: " . $materialWarehouse->name,
                        'description' => $requestData['description'],
                        'expected_date' => $expectedDate,
                        'status' => MaterialProposalStatusEnum::PENDING,
                        'reasoon_cancel' => null,
                    ];

                    $materialOutByProposal->update($dataMaterialOut);
                    event(new UpdatedContentEvent(MATERIAL_PROPOSAL_OUT_MODULE_SCREEN_NAME, $request, $materialOutByProposal));

                    $arrNotiOut = [
                        'action' => 'cập nhật',
                        'permission' => "proposal-goods-issue.create",
                        'route' => route('proposal-goods-issue.list.out', $materialOutByProposal->id),
                        'status' => 'Chờ duyệt'
                    ];
                    send_notify_cms_and_tele($materialOutByProposal, $arrNotiOut);
                }else{
                    $lastOutId = !empty(MaterialOut::orderByDesc('id')->first()) ? MaterialOut::orderByDesc('id')->first()->id + 1 : 1;
                    $proposal_out_code = str_pad($lastOutId, 7, '0', STR_PAD_LEFT);

                    $dataMaterialOut = [
                        'general_order_code' => $requestData['general_order_code'] ?? '',
                        'warehouse_id' => $wh_departure_id,
                        'warehouse_type' => MaterialWarehouse::class,
                        'warehouse_out_id' => $requestData['warehouse_id'],
                        'issuer_id' => Auth::user()->id,
                        'invoice_issuer_name' => Auth::user()->name,
                        'proposal_code' => 'XK' . $proposal_out_code,
                        'warehouse_name' => $wh_departure_name,
                        'warehouse_address' => $wh_departure_address,
                        'quantity' => $totalQuantity,
                        'total_amount' => (int) $totalAmount,
                        'title' => "Xuất hàng đến kho: " . $materialWarehouse->name,
                        'description' => $requestData['description'],
                        'expected_date' => $expectedDate,
                        'status' => MaterialProposalStatusEnum::PENDING,
                        'reasoon_cancel' => null,
                        'proposal_purchase_id' => $proposal->id
                    ];

                    $materialOutByProposal = MaterialOut::create($dataMaterialOut);
                    event(new CreatedContentEvent(MATERIAL_PROPOSAL_OUT_MODULE_SCREEN_NAME, $request, $materialOutByProposal));
                    $arrNotiOut = [
                        'action' => 'tạo',
                        'permission' => "proposal-goods-issue.edit",
                        'route' => route('proposal-goods-issue.edit', $materialOutByProposal->id),
                        'status' => 'Chờ duyệt'
                    ];
                    send_notify_cms_and_tele($materialOutByProposal, $arrNotiOut);
                }
            }

            //Delete proposal purchase detail
            foreach ($proposal->proposalDetail as $key => $proposalDetail) {
                $proposalDetail->delete();
            }

            //Delete proposal material out detail
            if(!empty($materialOutByProposal) && !empty($materialOutByProposal->proposalOutDetail))
            {
                foreach ($materialOutByProposal->proposalOutDetail as $key => $materialOutDetail) {
                    $materialOutDetail->delete();
                }

                //Xóa đơn đề xuất xuất kho được tạo khi đề xuất nhập từ kho tới kho nếu đơn đề xuất nhập kho từ nhà cung cấp
                if($parameter == 'supplier'){
                    $materialOutByProposal->delete();
                    event(new DeletedContentEvent(MATERIAL_PLAN_MODULE_SCREEN_NAME, $request, $materialOutByProposal));
                }
            }

            foreach ($requestData["$parameter"]['material'] as $key => $material) {
                $supplier_id = null;
                $supplier_name = null;

                if($parameter == 'supplier')
                {
                    $supplier_id = $requestData['supplier_id'];
                    $supplier_name = Supplier::where(['id' => $requestData['supplier_id']])->first()?->name;
                }else{
                    if(isset($materialOutByProposal))
                    {
                        $dataOutDetail = [
                            'proposal_id' => $materialOutByProposal->id,
                            'material_code' => $material['code'],
                            'material_name' => !isset($material['name']) ? Material::where(['code' => $material['code']])->first()?->name : $material['name'],
                            'material_unit' => $material['unit'],
                            'material_quantity' => $material['quantity'],
                            'material_price' => Material::where(['code' => $material['code']])->first()?->price,
                        ];
                        MaterialOutDeatail::create($dataOutDetail);
                    }
                }

                $dataInsert = [
                    'proposal_id' => $proposal->id,
                    'supplier_id' => $supplier_id,
                    'supplier_name' => $supplier_name,
                    'material_code' => $material['code'],
                    'material_name' => !isset($material['name']) ? Material::where(['code' => $material['code']])->first()?->name : $material['name'],
                    'material_unit' => $material['unit'],
                    'material_quantity' => $material['quantity'],
                    'material_price' => Material::where(['code' => $material['code']])->first()?->price,
                    'material_id' => $material['material_id'],
                ];

                try{
                    MaterialProposalPurchaseDetail::create($dataInsert);
                }catch(Exception $err){
                    DB::rollBack();
                    throw new Exception($err->getMessage(), 1);
                }
            }

        }catch(Exception $err)
        {
            DB::rollBack();
            throw new Exception($err->getMessage(), 1);
        }

        //DB commit
        DB::commit();

        return $response
            ->setPreviousUrl(route('material-proposal-purchase.index'))
            ->setNextUrl(route('material-proposal-purchase.index'))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function destroy(MaterialProposalPurchase $proposal, Request $request, BaseHttpResponse $response)
    {
        try {
            abort_if($proposal->status == MaterialProposalStatusEnum::APPOROVED, 403);

            $proposal->delete();

            event(new DeletedContentEvent(MATERIAL_PROPOSAL_PURCHASE_MODULE_SCREEN_NAME, $request, $proposal));

            $arrNoti = [
                'action' => 'xóa',
                'permission' => "material-proposal-purchase.create",
                'route' => route('material-proposal-purchase.index'),
                'status' => 'Đã xoá'
            ];
            send_notify_cms_and_tele($proposal, $arrNoti);

            //Get material out by id proposal
            $materialOutByProposal = MaterialOut::where(['proposal_purchase_id' => $proposal->id])->first();
            if(!empty($materialOutByProposal))
            {
                $materialOutByProposal->delete();

                foreach ($materialOutByProposal->proposalOutDetail as $key => $item) {
                    $item->delete();
                }
            }

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {

            throw new Exception($exception->getMessage(), 1);

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

        $proposal = MaterialProposalPurchase::where(['id' => $id])->with('proposalDetail')->first();

        abort_if(empty($proposal), 403);

        abort_if($proposal->status == MaterialProposalStatusEnum::APPOROVED, 403);

        $this->pageTitle(__('Duyệt đơn đề xuất nhập kho'));

        return view('plugins/warehouse::material.proposal.receipt', compact('proposal'));
    }

    public function viewProposalByCode(int|string $id, BaseHttpResponse $response)
    {
        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css']);

        $proposal = MaterialProposalPurchase::where(['id' => $id])->with('proposalDetail')->first();
        $receipt = MaterialReceiptConfirm::where(['proposal_id' => $id])->with('receiptDetail')->first();

        $this->pageTitle('Thông tin phiếu đề xuất nhập kho');

        return view('plugins/warehouse::material.proposal.view', compact('proposal','receipt'));
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

    public function cancelProposalPurchase(int|string $id, Request $request, BaseHttpResponse $response)
    {
        $proposal = MaterialProposalPurchase::where(['id' => $id])->first();

        if(empty($proposal))
        {
            return $response->setError()->setMessage('Không tìm thấy đơn đề xuất này!!');
        }

        if($proposal->status == MaterialProposalStatusEnum::APPOROVED)
        {
            return $response->setError()->setMessage('Đơn đề xuất này đã được duyệt, không thể huỷ đơn!!');
        }
        $proposal->update([
            'reasoon_cancel' => $request->input()['reasoon'],
            'status' => MaterialProposalStatusEnum::DENIED,
            'invoice_confirm_name' => Auth::user()->name,
            'date_confirm' => Carbon::now()->format('Y-m-d')
        ]);
        event(new UpdatedContentEvent(MATERIAL_PROPOSAL_PURCHASE_MODULE_SCREEN_NAME, $request, $proposal));

        //Kiểm tra có đơn đề xuất xuất nào được tạo từ đơn này không? Nếu có thì hủy đơn.
        $proposalOut = MaterialOut::where(['proposal_purchase_id' => $proposal->id])->first();
        if(!empty($proposalOut))
        {
            $proposalOut->update([
                'reasoon_cancel' => $request->input()['reasoon'],
                'status' => MaterialProposalStatusEnum::DENIED,
                'invoice_confirm_name' => Auth::user()->name,
                'date_confirm' => Carbon::now()->format('Y-m-d')
            ]);
            event(new UpdatedContentEvent(MATERIAL_PROPOSAL_OUT_MODULE_SCREEN_NAME, $request, $proposalOut));
        }

        //Thông báo hủy đơn cho admin
        $arrNoti = [
            'action' => 'hủy',
            'permission' => "material-proposal-purchase.receipt",
            'route' => route('material-proposal-purchase.view.code', $proposal->id),
            'status' => 'Đã huỷ đơn'
        ];
        send_notify_cms_and_tele($proposal, $arrNoti);

        //Thông báo hủy đơn cho người tạo đơn
        $arrNotiCreator = [
            'action' => 'hủy',
            'permission' => "material-proposal-purchase.create",
            'route' => route('material-proposal-purchase.view.code', $proposal->id),
            'status' => 'Đã huỷ đơn'
        ];
        send_notify_cms_and_tele($proposal, $arrNotiCreator);

        return $response
        ->setPreviousUrl(route('material-proposal-purchase.index'))
        ->setNextUrl(route('material-proposal-purchase.index'))
        ->setMessage("Huỷ đơn đề xuất thành công!!");
    }
}
