<?php

namespace Botble\Warehouse\Http\Controllers;

use Botble\Warehouse\Enums\ProposalGoodIssueStatusEnum;
use Botble\Warehouse\Http\Requests\ConfirmMaterialProposalOut;
use Botble\Warehouse\Http\Requests\DeniRequest;
use Botble\Warehouse\Models\DetailBatchMaterial;
use Botble\Warehouse\Models\MaterialProposalPurchase;
use Botble\Warehouse\Models\MaterialProposalPurchaseDetail;
use Illuminate\Support\Facades\Auth;
use Botble\Base\Facades\Assets;
use Botble\Base\Models\AdminNotification;
use Botble\Warehouse\Enums\MaterialProposalStatusEnum;
use Botble\Warehouse\Http\Requests\MaterialPlanRequest;
use Botble\Warehouse\Models\MaterialOutConfirm;
use Botble\Warehouse\Models\MaterialOutConfirmDetail;
use Botble\Warehouse\Models\MaterialWarehouse;
use Botble\Warehouse\Models\Material;
use Botble\Warehouse\Models\MaterialOut;
use Botble\Base\Facades\PageTitle;
use Botble\Warehouse\Models\MaterialOutDeatail;
use Botble\Warehouse\Models\ProcessingHouse;
use Botble\Warehouse\Models\QuantityMaterialStock;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Botble\Warehouse\Tables\MaterialPlanTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Warehouse\Forms\MaterialOutForm;
use Botble\Base\Forms\FormBuilder;
use Illuminate\Support\Carbon;

class MaterialOutController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
        $this
            ->breadcrumb()
            ->add(trans('Đề xuất xuất kho'), route('proposal-goods-issue.index'));
    }
    public function index(MaterialPlanTable $table)
    {

        Assets::addScriptsDirectly([

            'vendor/core/plugins/warehouse/js/detail_plan_material.js',
        ]);

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        Assets::addScripts(['sortable'])->addScriptsDirectly(
            [
                'vendor/core/plugins/warehouse/js/proposal-material.js',
            ]
        )->addStylesDirectly([
                    'vendor/core/plugins/warehouse/css/InOutMaterial.css',
                ]);
        $this->pageTitle(trans('plugins/warehouse::warehouse.material_out.create'));

        return $formBuilder->create(MaterialOutForm::class)->renderForm();
        // return view('plugins/warehouse::material.PlanMaterial');
    }
    public function store(MaterialPlanRequest $request, BaseHttpResponse $response)
    {
        $requestData = $request->input();
        $general_order_code = $requestData['general_order_code'];
        $expectedDate = Carbon::createFromFormat('d-m-Y', $requestData['expected_date']);

        $totalAmount = 0;
        $quantity = 0;
        $warehose = MaterialWarehouse::where('id', $requestData['warehouse_name'])->first();
        $lastBatchId = !empty(MaterialOut::orderByDesc('id')->first()) ? MaterialOut::orderByDesc('id')->first()->id + 1 : 1;

        $COUNT_CODE = 7;

        $proposal_code = str_pad($lastBatchId, $COUNT_CODE, '0', STR_PAD_LEFT);
        foreach ($requestData['quantity'] as $key => $value) {

            $quantity += $requestData['quantity'][$key];
            $totalAmount += $requestData['quantity'][$key] * (int) $requestData['price'][$key];
        }
        if ($requestData['is_processing_house'] == 0) {
            $warehouse_type = MaterialWarehouse::class;
            $warehose_out_id = $requestData['warehouse_out'];
            $is_processing_house = 0;
        } else {
            $warehouse_type = ProcessingHouse::class;
            $warehose_out_id = $requestData['processing_house'];
            $is_processing_house = 1;
        }

        $data = [
            'warehouse_id' => $warehose->id,
            'warehouse_name' => $warehose->name,
            'invoice_issuer_name' => Auth::user()->name,
            'status' => 'pending',
            'title' => $requestData['title'],
            'warehouse_address' => $warehose->address,
            'warehouse_type' => $warehouse_type,
            'warehouse_out_id' => $warehose_out_id,
            'quantity' => $quantity,
            'total_amount' => $totalAmount,
            'is_processing_house' => $is_processing_house,
            'description' => $requestData['description'],
            'expected_date' => $expectedDate,
            'proposal_code' => 'XK' . $proposal_code,
            'general_order_code' => $general_order_code,
            'issuer_id' => Auth::user()->id,
        ];
        if (isset($requestData['warehouse_out'])) {
            $warehouse_out = MaterialWarehouse::where('id', $requestData['warehouse_out'])->first();
        }
        DB::beginTransaction();
        try {
            $total = 0;
            $materialOut = MaterialOut::query()->create($data);

            $arrNoti = [
                'action' => 'tạo',
                'permission' => "proposal-goods-issue.receipt",
                'route' => route('proposal-goods-issue.list.out', $materialOut->id),
                'status' => 'Chờ duyệt'
            ];
            send_notify_cms_and_tele($materialOut, $arrNoti);
            $lastProposalPurchaseId = !empty(MaterialProposalPurchase::orderByDesc('id')->first()) ? MaterialProposalPurchase::orderByDesc('id')->first()->id + 1 : 1;
            $do_dai = strlen((string) $lastProposalPurchaseId);
            $COUNT_CODE = 7;

            $lastCount = $COUNT_CODE - $do_dai;

            $proposal_code = '';

            for ($i = 0; $i < $lastCount; $i++) {
                $proposal_code .= '0';
            }
            if ($requestData['is_processing_house'] == 0) {
                $dataPurchase = [
                    'general_order_code' => $general_order_code,
                    'warehouse_id' => $warehouse_out->id,
                    'invoice_issuer_name' => Auth::user()->name,
                    'proposal_code' => 'NK' . $proposal_code . $lastProposalPurchaseId,
                    'wh_departure_id' => $warehose->id,
                    'wh_departure_name' => $warehose->name,
                    'is_from_supplier' => false,
                    'warehouse_name' => $warehouse_out->name,
                    'warehouse_address' => $warehose->address,
                    'quantity' => 0,
                    'total_amount' => 0,
                    'title' => "Nhập hàng từ kho: " . $warehose->name . ' đến kho: ' . $warehouse_out->name,
                    'expected_date' => now()->format('Y-m-d'),
                    'proposal_out_id' => $materialOut->id,
                ];
                $materialProposal = MaterialProposalPurchase::create($dataPurchase);

                $arrNoti = [
                    'action' => 'tạo',
                    'permission' => "material-proposal-purchase.receipt",
                    'route' => route('material-proposal-purchase.receipt', $materialProposal->id),
                    'status' => 'Chờ duyệt'
                ];
                send_notify_cms_and_tele($materialProposal, $arrNoti);
            }
            foreach ($requestData['material'] as $key => $value) {
                $material = Material::find($requestData['material'][$key]);
                $materialOutDetail = new MaterialOutDeatail();
                $materialOutDetail->material_code = $material->code;
                $materialOutDetail->proposal_id = $materialOut->id;
                $materialOutDetail->material_name = $material->name;
                $materialOutDetail->material_unit = $material->unit;
                $materialOutDetail->material_quantity = (int) $requestData['quantity'][$key];
                $materialOutDetail->material_price = $material->price;
                $materialOutDetail->save();
                if (isset($materialProposal)) {
                    $dataInsert = [
                        'proposal_id' => $materialProposal->id,
                        'supplier_id' => 0,
                        'supplier_name' => 0,
                        'material_code' => $material->code,
                        'material_name' => $material->name,
                        'material_unit' => $material->unit,
                        'material_quantity' => (int) $requestData['quantity'][$key],
                        'material_price' => Material::where(['code' => $material['code']])->first()?->price,
                        'material_id' => $material->id,
                    ];

                    $data = MaterialProposalPurchaseDetail::create($dataInsert);

                    $total += $requestData['quantity'][$key];
                }
            }
            if (isset($materialProposal)) {
                $materialProposal->update(['quantity' => $total]);
            }


            DB::commit();


            event(new CreatedContentEvent(MATERIAL_PLAN_MODULE_SCREEN_NAME, $request, $materialOut));

            return $response
                ->setPreviousUrl(route('proposal-goods-issue.index'))
                ->setNextUrl(route('proposal-goods-issue.index'))
                // ->setNextUrl(route('proposal-goods-issue.edit', $materialOut->getKey()))
                ->setMessage(trans('core/base::notices.create_success_message'));
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function edit(MaterialOut $materialPlan, FormBuilder $formBuilder, BaseHttpResponse $response)
    {
        if (
            $materialPlan->issuer_id == Auth::user()->id &&
            $materialPlan->status->toValue() != ProposalGoodIssueStatusEnum::APPROVED ||
            $materialPlan->status->toValue() != ProposalGoodIssueStatusEnum::CONFIRM
        ) {
            if ($materialPlan->proposalPurchase?->status->toValue() == MaterialProposalStatusEnum::PENDING || $materialPlan->is_processing_house == 1) {
                Assets::addScripts(['sortable'])->addScriptsDirectly(
                    [
                        'vendor/core/plugins/warehouse/js/edit-proposal-material.js',
                    ]
                )->addStylesDirectly([
                            'vendor/core/plugins/warehouse/css/InOutMaterial.css',
                        ]);
                $this->pageTitle(trans('plugins/warehouse::warehouse.material_out.edit', ['name' => $materialPlan->proposal_code]));

                return $formBuilder->create(MaterialOutForm::class, ['model' => $materialPlan])->renderForm();
            }
        }
        return $response
            ->setError()
            ->setMessage('Bạn không có quyền chỉnh sửa đơn đề xuất này');
    }

    public function update(MaterialOut $materialPlan, MaterialPlanRequest $request, BaseHttpResponse $response)
    {
        $requestData = $request->input();
        $expectedDate = Carbon::createFromFormat('d-m-Y', $requestData['expected_date']);
        $quantity = 0;
        $totalAmount = 0;
        $isProcessingHouse = $materialPlan->is_processing_house;
        foreach ($requestData['quantity'] as $key => $value) {
            $quantity += $requestData['quantity'][$key];
            $totalAmount += $requestData['quantity'][$key] * (int) $requestData['price'][$key];
        }
        $warehose = MaterialWarehouse::where('id', $requestData['warehouse_name'])->first();
        if ($requestData['is_processing_house'] != 1) {
            $warehouse_type = MaterialWarehouse::class;
            $warehouse_out_id = $requestData['warehouse_out'];
        } else {
            $warehouse_type = ProcessingHouse::class;
            $warehouse_out_id = $requestData['processing_house'];
        }
        $data = [
            'warehouse_id' => $warehose->id,
            'warehouse_name' => $warehose->name,
            'status' => 'pending',
            'title' => $requestData['title'],
            'warehouse_type' => $warehouse_type,
            'warehouse_out_id' => $warehouse_out_id,
            'quantity' => $quantity,
            'total_amount' => $totalAmount,
            'is_processing_house' => $requestData['is_processing_house'],
            'description' => $requestData['description'],
            'expected_date' => $expectedDate,
        ];

        DB::beginTransaction();
        try {
            $materialPlan->fill($data);
            $materialPlan->save();
            MaterialOutDeatail::where('proposal_id', $materialPlan->id)->delete();
            $lastProposalPurchaseId = !empty(MaterialProposalPurchase::orderByDesc('id')->first()) ? MaterialProposalPurchase::orderByDesc('id')->first()->id + 1 : 1;
            $do_dai = strlen((string) $lastProposalPurchaseId);
            $COUNT_CODE = 7;

            $lastCount = $COUNT_CODE - $do_dai;

            $proposal_code = '';

            for ($i = 0; $i < $lastCount; $i++) {
                $proposal_code .= '0';
            }

            if ($requestData['warehouse_out'] != 0) {
                $warehouse_out = MaterialWarehouse::where('id', $requestData['warehouse_out'])->first();
                $dataPurchase = [
                    'general_order_code' => $requestData['general_order_code'],
                    'warehouse_id' => $warehouse_out->id,
                    'invoice_issuer_name' => Auth::user()->name,
                    'wh_departure_id' => $warehose->id,
                    'wh_departure_name' => $warehose->name,
                    'is_from_supplier' => false,
                    'warehouse_name' => $warehouse_out->name,
                    'warehouse_address' => $warehose->address,
                    'quantity' => $quantity,
                    'total_amount' => $totalAmount,
                    'status' => 'pending',
                    'title' => "Nhập hàng từ kho: " . $warehose->name . ' đến kho: ' . $warehouse_out->name,
                    'invoice_confirm_name' => null,
                    'expected_date' => now()->format('Y-m-d'),
                    'proposal_out_id' => $materialPlan->id,
                ];
            }
            if ($requestData['is_processing_house'] == 0) {
                $materialPurchase = MaterialProposalPurchase::where('proposal_out_id', '=', $materialPlan->id)->first();

                if ($materialPurchase) {
                    $materialDetails = MaterialProposalPurchaseDetail::where('proposal_id', $materialPurchase->id)->get();
                    foreach ($materialDetails as $materialDetail) {
                        $materialDetail->delete();
                    }
                } else {
                    $materialPurchase = MaterialProposalPurchase::create(array_merge($dataPurchase, ['proposal_code' => 'NK' . $proposal_code . $lastProposalPurchaseId]));
                }
            } else {

                if ($isProcessingHouse == '0') {
                    $materialPurchase = MaterialProposalPurchase::where('proposal_out_id', '=', $materialPlan->id)->first();
                    $materialDetails = MaterialProposalPurchaseDetail::where('proposal_id', $materialPurchase->id)->get();
                    foreach ($materialDetails as $materialDetail) {
                        $materialDetail->delete();
                    }
                    $materialPurchase->delete();
                }
            }
            foreach ($requestData['material'] as $key => $value) {
                $material = Material::find($requestData['material'][$key]);
                $materialOutDetail = new MaterialOutDeatail();
                $materialOutDetail->material_code = $material->code;
                $materialOutDetail->proposal_id = $materialPlan->id;
                $materialOutDetail->material_name = $material->name;
                $materialOutDetail->material_unit = $material->unit;
                $materialOutDetail->material_quantity = (int) $requestData['quantity'][$key];
                $materialOutDetail->material_price = $material->price;
                $materialOutDetail->save();
                if ($requestData['is_processing_house'] == 0) {
                    $dataInsert = [
                        'proposal_id' => $materialPurchase->id,
                        'supplier_id' => 0,
                        'supplier_name' => 0,
                        'material_code' => $material->code,
                        'material_name' => $material->name,
                        'material_unit' => $material->unit,
                        'material_quantity' => (int) $requestData['quantity'][$key],
                        'material_price' => Material::where(['code' => $material['code']])->first()?->price,
                        'material_id' => $material->id,
                    ];
                    $data = MaterialProposalPurchaseDetail::create($dataInsert);
                }
            }
            $arrNoti = [
                'action' => 'đã chỉnh sửa',
                'permission' => "proposal-goods-issue.receipt",
                'route' => route('proposal-goods-issue.list.out', $materialPlan->id),
                'status' => 'Đã xoá'
            ];
            send_notify_cms_and_tele($materialPlan, $arrNoti);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
        }
        event(new UpdatedContentEvent(MATERIAL_PLAN_MODULE_SCREEN_NAME, $request, $materialPlan));
        return $response
            ->setNextUrl(route('proposal-goods-issue.index'))
            ->setMessage(trans('Chỉnh sửa thành công'));
    }

    public function destroy(MaterialOut $materialPlan, Request $request, BaseHttpResponse $response)
    {
        try {
            $materialProposal = MaterialProposalPurchase::where('proposal_out_id', $materialPlan->id)->first();
            if ($materialProposal) {
                $materialDetails = MaterialProposalPurchaseDetail::where('proposal_id', $materialProposal->id)->get();
                foreach ($materialDetails as $materialDetail) {
                    $materialDetail->delete();
                }
                $materialProposal->delete();
            }
            MaterialOutDeatail::where('proposal_id', $materialPlan->id)->delete();
            $materialPlan->delete();
            $arrNoti = [
                'action' => 'xóa',
                'permission' => "proposal-goods-issue.receipt",
                'route' => '',
                'status' => 'Đã xoá'
            ];
            send_notify_cms_and_tele($materialPlan, $arrNoti);
            event(new DeletedContentEvent(MATERIAL_PLAN_MODULE_SCREEN_NAME, $request, $materialPlan));
            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }


    public function editPlanByCode($id)
    {

        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/ecommerce/js/order.js',
                'vendor/core/plugins/warehouse/js/accept-proposal-out-material.js',
            ])
            ->addScripts(['blockui', 'input-mask']);

        $plans = MaterialOut::where(['code' => $id])->with('materials')->get();
        $this->pageTitle(trans('plugins/ecommerce::order.edit_order', ['code' => $id]));
        return view('plugins/warehouse::check_inventory.edit', compact('plans'));
    }

    public function listOutMaterial(int|string $id, BaseHttpResponse $response)
    {
        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/ecommerce/js/order.js',
                'vendor/core/plugins/warehouse/js/proposal-out.js',
            ])
            ->addScripts(['blockui', 'input-mask']);
        $proposal = MaterialOut::where(['id' => $id])->with('proposalOutDetail')->first();
        if ($proposal->status == 'pending') {
            $this->pageTitle(__('Duyệt đơn đề xuất xuất kho'));
            return view('plugins/warehouse::material.proposal.good-issue', compact('proposal'));
        } else {
            abort_if($proposal->status != 'pending', 403, 'Không thể truy cập đơn hàng');

        }
    }

    public function approveProposalGoodIssue(int|string $id, ConfirmMaterialProposalOut $request, BaseHttpResponse $response)
    {

        $requestData = $request->input();
        $expectedDate = Carbon::createFromFormat('d-m-Y', $requestData['expected_date']);
        DB::beginTransaction();
        try {
            $proposal = MaterialOut::where(['id' => $id])->first();

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
                'warehouse_id' => $proposal->warehouse_id,
                'invoice_issuer_name' => Auth::user()->name,
                'proposal_id' => $proposal->id,
                'warehouse_name' => $proposal->warehouse_name,
                'warehouse_address' => $proposal->warehouse_address,
                'quantity' => $totalQuantity,
                'total_amount' => (int) $totalAmount,
                'title' => $proposal->title,
                'description' => $requestData['description'],
                'expected_date' => $expectedDate,
                'general_order_code' => $proposal->general_order_code,
            ];

            try {
                $materialReceipt = MaterialOutConfirm::query()->create($dataReceipt);

                $arrNoti = [
                    'action' => 'tạo phiếu',
                    'permission' => "goods-issue-receipt.edit",
                    'route' => route('goods-issue-receipt.index'),
                    'status' => 'chờ xuất kho'
                ];
                send_notify_cms_and_tele($materialReceipt, $arrNoti);


                $arrNoti = [
                    'action' => 'duyệt đơn',
                    'permission' => "proposal-goods-issue.create",
                    'route' => route('proposal-goods-issue.view.code', $proposal->id),
                    'status' => 'đã duyệt'
                ];
                send_notify_cms_and_tele($materialReceipt, $arrNoti);

                // send_notify_cms_and_tele($materialReceipt, $actionName, $route);
                event(new CreatedContentEvent(MATERIAL_PROPOSAL_OUT_MODULE_SCREEN_NAME, $request, $materialReceipt));
            } catch (Exception $err) {
                dd($err);
                DB::rollBack();
            }
            // dd($materialReceipt);
            for ($i = 0; $i < count($requestData['material']); $i++) {

                $dataInsert = [
                    'out_id' => $materialReceipt->id,
                    'material_code' => $proposal->proposalOutDetail[$i]->material_code,
                    'material_name' => $proposal->proposalOutDetail[$i]->material_name,
                    'material_unit' => $proposal->proposalOutDetail[$i]->material_unit,
                    'material_quantity' => $requestData['material'][$proposal->proposalOutDetail[$i]->id]['quantity'],
                    'material_price' => $proposal->proposalOutDetail[$i]->material_price,
                ];
                $materialDetail = MaterialOutConfirmDetail::create($dataInsert);
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
                ->setPreviousUrl(route('proposal-goods-issue.index'))
                ->setNextUrl(route('proposal-goods-issue.index'))
                ->setMessage(trans('core/base::notices.create_success_message'));
        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
        }

    }
    public function deniedMaterialPlanOut(int|string $id, Request $request, BaseHttpResponse $response, array $args)
    {
        $materialOut = MaterialOut::findOrFail($id);
        $materialOut->update(['status' => 'denied']);
        return $response
            ->setPreviousUrl(route('proposal-goods-issue.index'))
            ->setNextUrl(route('proposal-goods-issue.index'))
            ->setMessage('Từ chối xuất nguyên phụ liệu');
    }
    public function viewProposalByCode(int|string $id, BaseHttpResponse $response)
    {
        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css']);

        $proposal = MaterialOut::where(['id' => $id])->with('proposalOutDetail')->first();
        $receipt = MaterialOutConfirm::where(['proposal_id' => $id])->with('proposalOutDetail')->first();

        $this->pageTitle(trans('plugins/ecommerce::order.edit_order', ['code' => $id]));

        return view('plugins/warehouse::material.proposal.view-out', compact('proposal', 'receipt'));
    }


    public function getMaterialOutInfo($id)
    {
        $warehouse = MaterialOut::where('id', '=', $id)->first();
        return response()->json(['process' => $warehouse], 200);
    }

    public function deniedMaterialOut($id, DeniRequest $request, MaterialOut $materialPlan, BaseHttpResponse $response)
    {
        $materialPlan = MaterialOut::find($id);
        $materialPlan->update(['status' => 'denied', 'reason' => $request->input('denyReason'), 'date_confirm' => now()]);
        $arrNoti = [
            'action' => 'từ chối',
            'permission' => "proposal-goods-issue.receipt",
            'route' => route('goods-issue-receipt.view', $materialPlan->id),
            'status' => 'từ chối'
        ];
        send_notify_cms_and_tele($materialPlan, $arrNoti);
        event(new UpdatedContentEvent(MATERIAL_PLAN_MODULE_SCREEN_NAME, $request, $materialPlan));

        return $response
            ->setPreviousUrl(route('proposal-goods-issue.index'))
            ->setNextUrl(route('proposal-goods-issue.index'))
            ->setError()
            ->setMessage(trans('Từ chối đơn hàng'));
    }
}
