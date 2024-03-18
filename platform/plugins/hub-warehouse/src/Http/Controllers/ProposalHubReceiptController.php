<?php

namespace Botble\HubWarehouse\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Http\Requests\HubApproveProposalRequest;
use Botble\HubWarehouse\Http\Requests\ProposalHubReceiptRequest;
use Botble\HubWarehouse\Models\HubIssue;
use Botble\HubWarehouse\Models\HubIssueDetail;
use Botble\HubWarehouse\Models\HubReceipt;
use Botble\HubWarehouse\Models\HubReceiptDetail;
use Botble\HubWarehouse\Models\HubUser;
use Botble\HubWarehouse\Models\ProposalHubReceipt;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\HubWarehouse\Models\ProposalHubReceiptDetail;
use Botble\HubWarehouse\Models\Warehouse;
use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;
use Botble\WarehouseFinishedProducts\Enums\ProposalReceiptProductEnum;
use Botble\WarehouseFinishedProducts\Models\ProductIssue;
use Botble\WarehouseFinishedProducts\Models\ProductIssueDetails;
use Botble\WarehouseFinishedProducts\Models\ProposalProductIssue;
use Botble\WarehouseFinishedProducts\Models\ProposalProductIssueDetails;
use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use Botble\HubWarehouse\Tables\ProposalHubReceiptTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\HubWarehouse\Forms\ProposalHubReceiptForm;
use Botble\Base\Forms\FormBuilder;
use Botble\HubWarehouse\Supports\ProposalHubReceiptHelper;
use Botble\WarehouseFinishedProducts\Supports\ProposalReceiptHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class ProposalHubReceiptController extends BaseController
{
    // private $preXK = 'XK';
    // private $preNK = 'NK';
    public function index(ProposalHubReceiptTable $table)
    {
        PageTitle::setTitle(trans('Danh sách'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('Tạo đơn đề xuất'));

        return $formBuilder->create(ProposalHubReceiptForm::class)->renderForm();
    }

    public function store(ProposalHubReceiptRequest $request, BaseHttpResponse $response)
    {
        $requestData = $request->input();
        if (isset($requestData['is_odd'])) {
            $isBatch = 0;
        } else {
            $isBatch = 1;
        }
        $warehouseReceipt = Warehouse::find($requestData['warehouse_receipt_id']);
        $totalQty = 0;
        foreach ($requestData['quantityBatch'] as $quantity) {
            $totalQty += $quantity['quantity'];
        }
        $expectedDate = Carbon::createFromFormat('Y-m-d', $requestData['expected_date']);



        $lastProposal = ProposalHubReceipt::orderByDesc('id')->first();
        $proposalCode = $lastProposal ? (int) $lastProposal->proposal_code + 1 : 1;
        $dataInsert = [
            'proposal_code' => $proposalCode,
            'general_order_code' => $requestData['general_order_code'],
            'title' => $requestData['title'],
            'warehouse_receipt_id' => $requestData['warehouse_receipt_id'],
            'warehouse_name' => $warehouseReceipt->name,
            'warehouse_address' => $warehouseReceipt->hub->address,
            'issuer_id' => Auth::user()->id,
            'invoice_issuer_name' => Auth::user()->name,
            'is_warehouse' => $requestData['is_warehouse'],
            'quantity' => $totalQty,
            'expected_date' => $expectedDate,
            'description' => $requestData['description'],
            'is_batch' => $isBatch
        ];
        switch (true) {
            case $requestData['is_warehouse'] == 0:
                $dataInsert['warehouse_id'] = $requestData['warehouse_product'];
                $dataInsert['warehouse_type'] = WarehouseFinishedProducts::class;
                break;

            case $requestData['is_warehouse'] == 1:
                $dataInsert['warehouse_id'] = $requestData['warehouseHub'];
                $dataInsert['warehouse_type'] = Warehouse::class;
                break;

            case $requestData['is_warehouse'] == 2:
                $dataInsert['warehouse_id'] = $requestData['warehouse_out'];
                $dataInsert['warehouse_type'] = Warehouse::class;
                break;

            default:
                $dataInsert['warehouse_id'] = $requestData['warehouse_receipt_id'];
                $dataInsert['warehouse_type'] = Warehouse::class;
                break;
        }
        DB::beginTransaction();
        try {
            $proposalHubReceipt = ProposalHubReceipt::query()->create($dataInsert);
            foreach ($requestData['quantityBatch'] as $key => $product) {
                $infoProduct = Product::find($key);
                $color = '';
                $size = '';
                foreach ($infoProduct->variationProductAttributes as $attribute) {
                    if ($attribute->color) {
                        $color = $attribute->title;
                    }
                    else
                    {
                        $size = $attribute->title;
                    }
                }
                $dataDetail = [
                    'proposal_id' => $proposalHubReceipt->id,
                    'product_id' => $key,
                    'product_name' => $infoProduct->name,
                    'sku' => $infoProduct->sku,
                    'price' => $infoProduct->price,
                    'quantity' => $product['quantity'],
                    'color' => $color,
                    'size' => $size,
                    'is_batch' => $product['is_batch']
                ];
                $d = ProposalHubReceiptDetail::query()->create($dataDetail);
            }
            $arrNoti = [
                'action' => 'tạo',
                'permission' => "proposal-hub-receipt.approve",
                'route' => route('proposal-hub-receipt.approveView', $proposalHubReceipt->id),
                'status' => 'tạo'
            ];
            send_notify_cms_and_tele($proposalHubReceipt, $arrNoti);
            DB::commit();
            event(new CreatedContentEvent(PROPOSAL_HUB_RECEPIT_MODULE_SCREEN_NAME, $request, $proposalHubReceipt));

            return $response
                ->setPreviousUrl(route('proposal-hub-receipt.index'))
                ->setNextUrl(route('proposal-hub-receipt.index'))
                ->setMessage(trans('core/base::notices.create_success_message'));
        } catch (Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }
    }

    public function edit(ProposalHubReceipt $proposalHubReceipt, FormBuilder $formBuilder, BaseHttpResponse $response)
    {
        $proposalHubReceipt = $this->filterHub($proposalHubReceipt);
        PageTitle::setTitle(trans('Sửa đơn đề xuất :name', ['name' => $proposalHubReceipt->proposal_code]));
        if (
            $proposalHubReceipt->issuer_id == Auth::user()->id && $proposalHubReceipt->status->toValue() != ProposalProductEnum::APPOROVED
            && $proposalHubReceipt->status->toValue() != ProposalProductEnum::CONFIRM
            && $proposalHubReceipt->status->toValue() != ProposalProductEnum::REFUSE
        ) {
            return $formBuilder->create(ProposalHubReceiptForm::class, ['model' => $proposalHubReceipt])->renderForm();
        } else {
            return $response
                ->setError()
                ->setMessage(__('Đơn hàng đã duyệt hoặc từ chối!'));
        }
    }

    public function update(ProposalHubReceipt $proposalHubReceipt, ProposalHubReceiptRequest $request, BaseHttpResponse $response)
    {
        if ($proposalHubReceipt->status != ProposalReceiptProductEnum::PENDING) {
            return $response
                ->setError()
                ->setMessage('Đơn không thể thay đổi');
        }
        $requestData = $request->input();
        if (isset($requestData['is_odd'])) {
            $isBatch = 0;
        } else {
            $isBatch = 1;
        }
        $warehouseReceipt = Warehouse::find($requestData['warehouse_receipt_id']);
        $expectedDate = Carbon::createFromFormat('Y-m-d', $requestData['expected_date']);
        $totalQty = 0;
        foreach ($requestData['quantityBatch'] as $quantity) {
            $totalQty += $quantity['quantity'];
        }
        $dataInsert = [
            'general_order_code' => $requestData['general_order_code'],
            'title' => $requestData['title'],
            'warehouse_receipt_id' => $requestData['warehouse_receipt_id'],
            'warehouse_name' => $warehouseReceipt->name,
            'warehouse_address' => $warehouseReceipt->hub->address,
            'issuer_id' => Auth::user()->id,
            'invoice_issuer_name' => Auth::user()->name,
            'is_warehouse' => $requestData['is_warehouse'],
            'quantity' => $totalQty,
            'expected_date' => $expectedDate,
            'description' => $requestData['description'],
            'is_batch' => $isBatch
        ];

        switch (true) {
            case $requestData['is_warehouse'] == 0:
                $dataInsert['warehouse_id'] = $requestData['warehouse_product'];
                $dataInsert['warehouse_type'] = WarehouseFinishedProducts::class;
                break;
            case $requestData['is_warehouse'] == 1:
                $dataInsert['warehouse_id'] = $requestData['warehouseHub'];
                $dataInsert['warehouse_type'] = Warehouse::class;
                break;

            case $requestData['is_warehouse'] == 2:
                $dataInsert['warehouse_id'] = $requestData['warehouse_out'];
                $dataInsert['warehouse_type'] = Warehouse::class;
                break;

            default:
                $dataInsert['warehouse_id'] = $requestData['warehouse_receipt_id'];
                $dataInsert['warehouse_type'] = Warehouse::class;
                break;
        }
        DB::beginTransaction();
        try {
            $proposalHubReceipt->fill($dataInsert);
            $proposalHubReceipt->save();
            $proposalHubReceiptDetails = ProposalHubReceiptDetail::where('proposal_id', $proposalHubReceipt->id)->get();
            foreach ($proposalHubReceiptDetails as $proposalReceiptDetail) {
                $proposalReceiptDetail->delete();
            }
            foreach ($requestData['quantityBatch'] as $key => $product) {
                $infoProduct = Product::find($key);
                $color = '';
                $size = '';
                $arrAttribute = $infoProduct->variationProductAttributes;
                if (count($arrAttribute) > 0) {
                    if (count($arrAttribute) === 1) {
                        $color = $arrAttribute[0]->color == null ? '' : $arrAttribute[0]->title;
                        $size = $arrAttribute[0]->color == null ? $arrAttribute[0]->title : '';
                    } else if (count($arrAttribute) === 2) {
                        $color = $arrAttribute[0]->color == null ? $arrAttribute[1]->title : $arrAttribute[0]->title;
                        $size = $arrAttribute[0]->color == null ? $arrAttribute[0]->title : $arrAttribute[1]->title;
                    }
                }

                $dataDetail = [
                    'proposal_id' => $proposalHubReceipt->id,
                    'product_id' => $key,
                    'product_name' => $infoProduct->name,
                    'sku' => $infoProduct->sku,
                    'price' => $infoProduct->price,
                    'quantity' => $product['quantity'],
                    'size' => $size,
                    'color' => $color,
                    'is_batch' => $product['is_batch']
                ];
                ProposalHubReceiptDetail::query()->create($dataDetail);
            }
            $arrNoti = [
                'action' => 'cập nhật',
                'permission' => "proposal-hub-receipt.approve",
                'route' => route('proposal-hub-receipt.approveView', $proposalHubReceipt->id),
                'status' => 'cập nhật'
            ];
            send_notify_cms_and_tele($proposalHubReceipt, $arrNoti);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }


        event(new UpdatedContentEvent(PROPOSAL_HUB_RECEPIT_MODULE_SCREEN_NAME, $request, $proposalHubReceipt));

        return $response
            ->setPreviousUrl(route('proposal-hub-receipt.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(ProposalHubReceipt $proposalHubReceipt, Request $request, BaseHttpResponse $response)
    {
        DB::beginTransaction();

        try {
            DB::commit();

            $proposalDetails = $proposalHubReceipt->proposalReceiptDetail;
            foreach ($proposalDetails as $proposalDetail) {
                $proposalDetail->delete();
            }
            $proposalHubReceipt->delete();

            event(new DeletedContentEvent(PROPOSAL_HUB_RECEPIT_MODULE_SCREEN_NAME, $request, $proposalHubReceipt));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
    public function approve(int $id, BaseHttpResponse $response)
    {
        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/ecommerce/js/order.js',
                'vendor/core/plugins/hub-warehouse/js/approve-product-receipt.js',
            ])
            ->addScripts(['blockui', 'input-mask']);
        $proposal = ProposalHubReceipt::where(['id' => $id])->with('proposalReceiptDetail.product')->first();
        $proposal = $this->filterHub($proposal);
        if ($proposal->status != ProposalProductEnum::PENDING) {
            return $response
                ->setError()
                ->setMessage(__('Đơn hàng đã duyệt hoặc từ chối!'));
        }
        // dd($proposal->proposalProductIssueDetail);
        $this->pageTitle(__('Duyệt đơn đề xuất nhập kho HUB'));
        return view('plugins/hub-warehouse::proposal-receipt.approve', compact('proposal'));
    }
    public function approveProposal(HubApproveProposalRequest $request, BaseHttpResponse $response)
    {
        $requestData = $request->input();
        $expectedDate = Carbon::createFromFormat('d-m-Y', $requestData['expected_date']);
        DB::beginTransaction();
        $proposal = ProposalHubReceipt::where('id', $requestData['proposal_id'])->sharedLock()->first();
        if ($proposal) {
            if ($proposal->status != ProposalProductEnum::PENDING) {
                DB::rollBack();
                return $response
                    ->setError()
                    ->setMessage(__('Đơn hàng đã duyệt hoặc từ chối!'));
            }
            try {
                if ($proposal->is_warehouse == 3) {
                    $proposal->update([
                        'status' => ProposalReceiptProductEnum::CONFIRM,
                        'invoice_confirm_name' => Auth::user()->name,
                        'date_confirm' => Carbon::now()->format('Y-m-d'),
                    ]);

                } else {
                    $proposal->update([
                        'status' => ProposalReceiptProductEnum::WAIT,
                        'invoice_confirm_name' => Auth::user()->name,
                        'date_confirm' => Carbon::now()->format('Y-m-d'),
                    ]);
                }
                $totalAmount = 0;
                $totalQuantity = 0;
                foreach ($requestData['product'] as $key => $product) {
                    $totalAmount += ($product['quantity'] * $product['price']);
                    $totalQuantity += ((int) $product['quantity']);
                }
                $dataProductIssue = [
                    'proposal_id' => $proposal->id,
                    'warehouse_id' => $proposal->warehouse_id,
                    'issuer_id' => Auth::user()->id,
                    'invoice_issuer_name' => Auth::user()->name,
                    'quantity' => $totalQuantity,
                    'title' => $proposal->title,
                    'description' => $requestData['description'],
                    'expected_date' => $expectedDate,
                    'general_order_code' => $proposal->general_order_code,
                    'proposal_code' => $proposal->proposal_code,
                    'status' => 'pending',
                    'is_batch' => $proposal->is_batch
                ];

                if ($proposal->warehouse_receipt_id === $proposal->warehouse_id && $proposal->warehouse_type === Warehouse::class) {
                    $lastReceipt = HubReceipt::orderByDesc('id')->first();
                    $receiptCode = $lastReceipt ? (int) $lastReceipt->receipt_code + 1 : 1;
                    $hubReceipt = HubReceipt::query()->create(array_merge($dataProductIssue, [
                        'warehouse_name' => $proposal->warehouse_name,
                        'warehouse_receipt_id' => $proposal->warehouse_receipt_id,
                        'warehouse_address' => $proposal->warehouse_address,
                        'warehouse_type' => $proposal->warehouse_type,
                        'receipt_code' => $receiptCode
                    ]));
                    $arrNoti = [
                        'action' => 'duyệt',
                        'permission' => "hub-issue.confirm",
                        'route' => route('hub-receipt.confirm', $hubReceipt->id),
                        'status' => 'duyệt'
                    ];
                    send_notify_cms_and_tele($hubReceipt, $arrNoti);
                } else {
                    if ($proposal->warehouse_type === Warehouse::class) {
                        $lastIssue = HubIssue::orderByDesc('id')->first();
                        $issueCode = $lastIssue ? (int) $lastIssue->issue_code + 1 : 1;
                        $hubIssue = HubIssue::query()->create([
                            'warehouse_issue_id' => $proposal->warehouse_id,
                            'proposal_id' => $proposal->id,
                            'warehouse_name' => $proposal->warehouse->name,
                            'warehouse_address' => $proposal->warehouse->hub->address,
                            'issuer_id' => $proposal->issuer_id,
                            'invoice_issuer_name' => $proposal->invoice_issuer_name,
                            'warehouse_id' => $proposal->warehouse_receipt_id,
                            'warehouse_type' => Warehouse::class,
                            'general_order_code' => $proposal->general_order_code,
                            'from_proposal_receipt' => 1,
                            'title' => 'Xuất kho qua ' . $proposal->warehouse_name . ' - ' . $proposal->warehouseReceipt->hub->name,
                            'description' => $proposal->description,
                            'expected_date' => $proposal->expected_date,
                            'issue_code' => $issueCode,
                            'is_batch' => $proposal->is_batch

                        ]);
                        $arrNoti = [
                            'action' => 'tạo',
                            'permission' => "hub-issue.confirm",
                            'route' => route('hub-issue.confirm', $hubIssue),
                            'status' => 'tạo'
                        ];
                        send_notify_cms_and_tele($hubIssue, $arrNoti);
                    } else {
                        $lastProductIssue = ProductIssue::orderByDesc('id')->first();
                        $issueCode = $lastProductIssue ? (int) $lastProductIssue->issue_code + 1 : 1;
                        $productIssue = ProductIssue::query()->create(array_merge($dataProductIssue, [
                            'warehouse_issue_type' => WarehouseFinishedProducts::class,
                            'warehouse_name' => $proposal->warehouse->name,
                            'warehouse_receipt_id' => $proposal->warehouse_receipt_id,
                            'warehouse_address' => $proposal->warehouse_address,
                            'warehouse_type' => Warehouse::class,
                            'from_proposal_receipt' => 1,
                            'is_warehouse' => 0,
                            'issue_code' => $issueCode
                        ]));
                        $arrNoti = [
                            'action' => 'tạo',
                            'permission' => "hub-issue.confirm",
                            'route' => route('hub-issue.confirm', $productIssue),
                            'status' => 'tạo'
                        ];
                        send_notify_cms_and_tele($productIssue, $arrNoti);
                    }
                }
                foreach ($requestData['product'] as $key => $product) {
                    $products = Product::find($key);
                    $dataCommon = [
                        'product_id' => $key,
                        'product_name' => $products->name,
                        'sku' => $products->sku,
                        'price' => $products->price,
                        'quantity' => $product['quantity'],
                        'color' => $product['color'],
                        'size' => $product['size'],
                        'is_batch' => $product['size'] ? 0 : 1,
                    ];
                    if ($proposal->warehouse_receipt_id === $proposal->warehouse_id && $proposal->warehouse_type === Warehouse::class) {
                        $dataCommon['hub_receipt_id'] = $hubReceipt->id;
                        HubReceiptDetail::query()->create($dataCommon);
                    } else {
                        if ($proposal->warehouse_type === Warehouse::class) {
                            $dataCommon['hub_issue_id'] = $hubIssue->id;
                            HubIssueDetail::query()->create($dataCommon);

                        } else {
                            $dataCommon['product_issue_id'] = $productIssue->id;
                            ProductIssueDetails::query()->create($dataCommon);
                        }
                    }


                }
                DB::commit();
                return $response
                    ->setPreviousUrl(route('proposal-hub-receipt.index'))
                    ->setNextUrl(route('proposal-hub-receipt.index'))
                    ->setMessage(trans('Đã duyệt'));

            } catch (Exception $e) {
                DB::rollBack();
                return $response
                    ->setError()
                    ->setMessage($e->getMessage());

            }

        } else {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage('Không tồn tại đơn hàng');
        }
    }
    public function getProduct($proposal_id)
    {
        $proposal = ProposalHubReceipt::find($proposal_id);
        $products = ProposalHubReceiptDetail::where('proposal_id', $proposal_id)
            ->with([
                'product',
                'productStock' => function ($q) use ($proposal)
                {
                    $q->where('stock_id',$proposal->warehouse_receipt_id);
                },
                'product.parentProduct'
            ]
            )
            ->get();
        $idHubReceipt = $proposal->warehouseReceipt->hub->id;
        $warehouse_issue = $proposal->warehouse;
        return response()->json(['data' => $products, 'proposal' => $proposal, 'idHubReceipt' => $idHubReceipt, 'warehouseIssue' => $warehouse_issue], 200);
    }
    public function view(int|string $id)
    {
        $proposal = ProposalHubReceipt::find($id);
        $proposal = $this->filterHub($proposal);
        $this->pageTitle('Thông tin nhập kho');
        $receipt = HubReceipt::where('proposal_id', $proposal->id)->first();
        return view('plugins/hub-warehouse::proposal-receipt.view', compact('proposal', 'receipt'));
    }
    private function filterHub($q)
    {
        $authUserId = request()->user()->id;
        $userHub = HubUser::where('user_id', $authUserId)->pluck('hub_id')->toArray();

        if (!request()->user()->hasPermission('hub-warehouse.all-permissions')) {
            if (!request()->user()->hasPermission('proposal-hub-receipt.approve')) {
                $q->where('issuer_id', $authUserId);
            }
            $hubId = $q->warehouseReceipt->hub->id;
            if (!in_array($hubId, $userHub)) {
                abort(403, 'Không có quyền truy cập kho này');
            }
        }

        return $q;
    }
    public function denied($id, Request $request, BaseHttpResponse $response)
    {
        $proposal = ProposalHubReceipt::find($id);
        $proposal->status = ProposalProductEnum::DENIED;
        $proposal->invoice_confirm_name = Auth::user()->name;
        $proposal->reason_cancel = $request->input('denyReason');
        $proposal->save();
        $arrNoti = [
            'action' => 'từ chối',
            'permission' => "proposal-hub-receipt.create",
            'route' => route('proposal-hub-receipt.approveView', $proposal->id),
            'status' => 'từ chối'
        ];
        send_notify_cms_and_tele($proposal, $arrNoti);
        return $response
            ->setPreviousUrl(route('proposal-hub-receipt.index'))
            ->setNextUrl(route('proposal-hub-receipt.index'))
            ->setMessage(trans('Đã từ chối'));
    }

    public function getGenerateReceiptProduct(Request $request, ProposalHubReceiptHelper $hubReceiptHelper)
    {
        $data = ProposalHubReceipt::with('proposalReceiptDetail')->find($request->id);

        if ($request->button_type === 'print') {
            return $hubReceiptHelper->streamInvoice($data);
        }
        return $hubReceiptHelper->downloadInvoice($data);
    }
}
