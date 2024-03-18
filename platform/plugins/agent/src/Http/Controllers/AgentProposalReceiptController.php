<?php

namespace Botble\Agent\Http\Controllers;

use Botble\Agent\Enums\ProposalAgentEnum;
use Botble\Agent\Http\Requests\ProposalAgentReceiptRequest;
use Botble\Agent\Models\Agent;
use Botble\Agent\Models\AgentWarehouse;
use Botble\Agent\Models\ProposalAgentReceipt;
use Botble\Agent\Models\ProposalAgentReceiptDetail;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Models\HubIssue;
use Botble\HubWarehouse\Models\HubIssueDetail;
use Botble\HubWarehouse\Models\ProposalHubIssue;
use Botble\HubWarehouse\Models\ProposalHubIssueDetail;
use Botble\HubWarehouse\Models\QuantityProductInStock;
use Botble\HubWarehouse\Models\Warehouse;
use Botble\WarehouseFinishedProducts\Enums\ProductBatchStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Illuminate\Http\Request;
use Exception;
use Botble\Agent\Tables\ProposalAgentReceiptTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Agent\Forms\ProposalAgentReceiptForm;
use Botble\Agent\Models\AgentReceipt;
use Botble\Agent\Models\ReceiptProduct;
use Botble\Agent\Supports\ProposalReceiptHelper;
use Botble\Base\Forms\FormBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AgentProposalReceiptController extends BaseController
{
    // private $preXK = 'XK';
    // private $preNK = 'NK';
    public function index(ProposalAgentReceiptTable $table)
    {
        PageTitle::setTitle(trans('Danh sách'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('Tạo đơn đề xuất'));

        return $formBuilder->create(ProposalAgentReceiptForm::class)->renderForm();
    }

    private function buildProposalData($requestData, $warehouseName, $warehouseAddress, $total)
    {
        return [
            'warehouse_name' => $warehouseName,
            'warehouse_address' => $warehouseAddress,
            'issuer_id' => Auth::user()->id,
            'invoice_issuer_name' => Auth::user()->name,
            'general_order_code' => $requestData['general_order_code'],
            'title' => $requestData['title'],
            'description' => $requestData['description'],
            'is_warehouse' => 4,
            'expected_date' => Carbon::createFromFormat('Y-m-d', $requestData['expected_date']),
            'quantity' => $total,
        ];
    }
    public function store(ProposalAgentReceiptRequest $request, BaseHttpResponse $response)
    {
        $requestData = $request->input();
        // tính total
        $totals = 0;
        $itemOdd = json_decode($requestData['itemOdd'], true);
        foreach ($itemOdd as $item) {
            if (isset($item['quantity'])) {
                $totals += $item['quantity'];
            }
        }
        // lấy giá trị cuối của proposal_code
        $lastProposalReceipt = ProposalAgentReceipt::orderByDesc('id')->first();
        $proposalReceiptCode = $lastProposalReceipt ? (int) $lastProposalReceipt->proposal_code + 1 : 1;
        $angetWarehouse = AgentWarehouse::find($requestData['warehouse_receipt_id']);

        // lấy giá trị cuối của proposal_code
        $lastProposalHubIssue = ProposalHubIssue::orderByDesc('id')->first();
        $proposalCode = $lastProposalHubIssue ? (int) $lastProposalHubIssue->proposal_code + 1 : 1;
        $agent = Agent::find($requestData['agent_id']);
        $hubWarehouse = $agent->hub?->warehouseInHub->first();
        if ($hubWarehouse) {
            $dataCreateIssue = $this->buildProposalData($requestData, $hubWarehouse->name, $hubWarehouse->hub->address, $totals);
        } else {
            return $response->setError()
                ->setMessage(trans('Trong hub không có kho'));
        }

        $dataCreate = $this->buildProposalData($requestData, $angetWarehouse->name, $angetWarehouse->address, $totals);

        DB::beginTransaction();
        try {
            $proposalAgentReceipt = ProposalAgentReceipt::query()->create(array_merge($dataCreate, [
                'warehouse_receipt_id' => $requestData['warehouse_receipt_id'],
                'proposal_code' => $proposalReceiptCode,
                'warehouse_type' => Warehouse::class,
                'warehouse_id' => $hubWarehouse->id,
            ]));
            $proposalHubIssue = ProposalHubIssue::query()->create(array_merge($dataCreateIssue, [
                'warehouse_id' => $requestData['warehouse_receipt_id'],
                'warehouse_issue_id' => $hubWarehouse->id,
                'warehouse_type' => AgentWarehouse::class,
                'proposal_receipt_id' => $proposalAgentReceipt->id,
                'is_batch' => 1,
                'proposal_code' => $proposalCode,
            ]));
            // foreach ($requestData['product'] as $key => $productReceipt) {

            //     $product = Product::find($key);
            //     $data = [
            //         'product_id' => $key,
            //         'product_name' => $product->name,
            //         'sku' => $product->sku,
            //         'price' => $product->price,
            //         'quantity' => $productReceipt['quantity'],
            //     ];
            //     ProposalAgentReceiptDetail::query()->create(array_merge($data, [
            //         'proposal_id' => $proposalAgentReceipt->id,
            //     ]));
            //     ProposalHubIssueDetail::query()->create(array_merge($data, [
            //         'proposal_id' => $proposalHubIssue->id,
            //         'is_batch' => 1
            //     ]));
            // }
            foreach ($itemOdd as $value) {
                $product = Product::find($value['id']);
                $color = '';
                $size = '';
                foreach ($product->variationProductAttributes as $attribute) {
                    if ($attribute->color) {
                        $color = $attribute->title;
                    }
                    else
                    {
                        $size = $attribute->title;
                    }
                }
                $data = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'color' => $color,
                    'size' => $size,
                    'price' => $product->price,
                    'sku' => $product->sku,
                    'quantity' => (int) $value['quantity'],
                ];
                ProposalHubIssueDetail::query()->create(array_merge($data, [
                    'proposal_id' => $proposalHubIssue->id,
                    'is_batch' => (int) 0,
                ]));

                ProposalAgentReceiptDetail::query()->create(array_merge($data, [
                    'proposal_id' => $proposalAgentReceipt->id,
                ]));
            }
            $arrNoti = [
                'action' => 'tạo',
                'permission' => "proposal-agent-receipt.create",
                'route' => route('proposal-agent-receipt.view', $proposalAgentReceipt->id),
                'status' => 'tạo'
            ];
            send_notify_cms_and_tele($proposalAgentReceipt, $arrNoti);
            $arrNoti = [
                'action' => 'tạo',
                'permission' => "proposal-hub-issue.approve",
                'route' => route('proposal-hub-issue.approveProposalProductIssue', $proposalHubIssue->id),
                'status' => 'tạo'
            ];
            send_notify_cms_and_tele($proposalHubIssue, $arrNoti);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }

        event(new CreatedContentEvent(PROPOSAL_AGENT_RECEIPT_MODULE_SCREEN_NAME, $request, $proposalAgentReceipt));

        return $response
            ->setPreviousUrl(route('proposal-agent-receipt.index'))
            ->setNextUrl(route('proposal-agent-receipt.edit', $proposalAgentReceipt->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(ProposalAgentReceipt $proposalAgentReceipt, FormBuilder $formBuilder, BaseHttpResponse $response)
    {
        abort_if(check_user_depent_of_agent($proposalAgentReceipt->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        PageTitle::setTitle(trans('Sửa đơn đề xuất :name', ['name' => BaseHelper::clean(get_proposal_receipt_product_code($proposalAgentReceipt->proposal_code))]));
        if ($proposalAgentReceipt->status != ProposalAgentEnum::PENDING) {
            return $response
                ->setError()
                ->setMessage('Đơn hàng đã duyệt hoặc từ chối!');
        }
        return $formBuilder->create(ProposalAgentReceiptForm::class, ['model' => $proposalAgentReceipt])->renderForm();
    }

    public function update(ProposalAgentReceipt $proposalAgentReceipt, ProposalAgentReceiptRequest $request, BaseHttpResponse $response)
    {
        abort_if(check_user_depent_of_agent($proposalAgentReceipt->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');
        if($proposalAgentReceipt->status != ProposalAgentEnum::PENDING)
        {
            return $response
            ->setError()
            ->setMessage('Đơn không thể thay đổi');
        }
        $requestData = $request->input();
        $totals = 0;
        $itemOdd = json_decode($requestData['itemOdd'], true);
        foreach ($itemOdd as $item) {
            if (isset($item['quantity'])) {
                $totals += $item['quantity'];
            }
        }

        $angetWarehouse = AgentWarehouse::find($requestData['warehouse_receipt_id']);
        // create Data Update
        $dataUpdateReceipt = $this->buildProposalData($requestData, $angetWarehouse->name, $angetWarehouse->address, $totals);
        // create data Update proposal receipt
        $agent = Agent::find($requestData['agent_id']);
        $hubWarehouse = $agent->hub?->warehouseInHub->first();
        if ($hubWarehouse) {
            $dataUpdateIssue = $this->buildProposalData($requestData, $hubWarehouse->name, $hubWarehouse->hub->address, $totals);
        } else {
            return $response->setError()
                ->setMessage(trans('Trong hub không có kho hoặc đại lý không có hub'));
        }

        // Find Proposal Issue
        $proposalHubIssue = ProposalHubIssue::where([
            'warehouse_type' => AgentWarehouse::class,
            'proposal_receipt_id' => $proposalAgentReceipt->id
        ])->first();
        DB::beginTransaction();
        try {
            // update
            $proposalHubIssue->update(array_merge($dataUpdateIssue, [
                'warehouse_issue_id' => $hubWarehouse->id,
                'warehouse_id' => $requestData['warehouse_receipt_id'],

            ]));
            foreach ($proposalHubIssue->proposalHubIssueDetail as $proposalHubIssueDetail) {
                $proposalHubIssueDetail->delete();
            }
            $proposalAgentReceipt->update(
                array_merge(
                    $dataUpdateReceipt,
                    [
                        'warehouse_receipt_id' => $requestData['warehouse_receipt_id'],
                        'warehouse_id' => $hubWarehouse->id,
                    ]
                )
            );
            $proposalAgentReceiptDetails = ProposalAgentReceiptDetail::where('proposal_id', $proposalAgentReceipt->id)->get();
            foreach ($proposalAgentReceiptDetails as $proposalReceiptDetail) {
                $proposalReceiptDetail->delete();
            }
            foreach ($itemOdd as $value) {
                $product = Product::find($value['id']);
                $color = '';
                $size = '';

                foreach ($product->variationProductAttributes as $attribute) {
                    if ($attribute->color) {
                        $color = $attribute->title;
                    }
                    else
                    {
                        $size = $attribute->title;
                    }
                }

                $data = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'color' => $color,
                    'size' => $size,
                    'price' => $product->price,
                    'sku' => $product->sku,
                    'quantity' => (int) $value['quantity'],
                ];
                ProposalHubIssueDetail::query()->create(array_merge($data, [
                    'proposal_id' => $proposalHubIssue->id,
                    'is_batch' => (int) 0,
                ]));

                ProposalAgentReceiptDetail::query()->create(array_merge($data, [
                    'proposal_id' => $proposalAgentReceipt->id,
                ]));
            }
            $arrNoti = [
                'action' => 'chỉnh sửa',
                'permission' => "proposal-agent-receipt.create",
                'route' => route('proposal-agent-receipt.view', $proposalAgentReceipt->id),
                'status' => 'chỉnh sửa'
            ];
            send_notify_cms_and_tele($proposalAgentReceipt, $arrNoti);
            $arrNoti = [
                'action' => 'chỉnh sửa',
                'permission' => "proposal-hub-issue.approve",
                'route' => route('proposal-hub-issue.approveProposalProductIssue', $proposalHubIssue->id),
                'status' => 'chỉnh sửa'
            ];
            send_notify_cms_and_tele($proposalHubIssue, $arrNoti);
            DB::commit();
            event(new UpdatedContentEvent(PROPOSAL_AGENT_RECEIPT_MODULE_SCREEN_NAME, $request, $proposalAgentReceipt));

            return $response
                ->setNextUrl(route('proposal-agent-receipt.index'))
                ->setPreviousUrl(route('proposal-agent-receipt.edit', $proposalAgentReceipt->getKey()))
                ->setMessage(trans('core/base::notices.update_success_message'));
        } catch (Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage(trans($e->getMessage()));
        }
    }

    public function destroy(ProposalAgentReceipt $proposalAgentReceipt, Request $request, BaseHttpResponse $response)
    {
        abort_if(check_user_depent_of_agent($proposalAgentReceipt->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        DB::beginTransaction();
        try {
            foreach ($proposalAgentReceipt->proposalIssue->proposalHubIssueDetail as $detailIssue) {
                $detailIssue->delete();
            }
            $proposalAgentReceipt->proposalIssue->delete();
            $proposalDetails = ($proposalAgentReceipt->proposalReceiptDetail);
            foreach ($proposalDetails as $proposalDetail) {
                $proposalDetail->delete();
            }
            $arrNoti = [
                'action' => 'xóa',
                'route' => '',
                'permission' => 'proposal-agent-receipt.create',
                'status' => 'xóa'
            ];
            send_notify_cms_and_tele($proposalAgentReceipt, $arrNoti);
            $proposalAgentReceipt->delete();
            DB::commit();
            event(new DeletedContentEvent(PROPOSAL_AGENT_RECEIPT_MODULE_SCREEN_NAME, $request, $proposalAgentReceipt));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
    public function proposal($id)
    {

        $proposal = ProposalAgentReceipt::where('id', $id)->first();
        $product = ProposalAgentReceiptDetail::where('proposal_id', $proposal->id)->with([
            'product',
            'product.parentProduct',
            'productHubStock' => function ($q) use ($proposal) {
                $q->where('stock_id', $proposal->warehouse->id);
            }
        ])->get();

        $agent = $proposal->warehouseReceipt->agent->id;
        return response()->json([
            'data' => $product,
            'proposal' => $proposal,
            'agent' => $agent
        ], 200);
    }
    public function approveView(ProposalAgentReceipt $proposal, BaseHttpResponse $response)
    {
        abort_if(check_user_depent_of_agent($proposal->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/ecommerce/js/order.js',
                'vendor/core/plugins/agent/js/approve-agent-receipt.js',
            ])
            ->addScripts(['blockui', 'input-mask']);
        if ($proposal->status != ProposalProductEnum::PENDING) {
            return $response
                ->setError()
                ->setMessage(__('Không có quyền truy cập! Đơn hàng đã được duyệt hoặc từ chối'));
        }
        $this->pageTitle(__('Duyệt đơn đề xuất nhập kho đại lý'));
        return view('plugins/agent::proposal-receipt.approve', compact('proposal'));
    }
    public function approve(Request $request, ProposalAgentReceipt $proposal, BaseHttpResponse $response)
    {
        abort_if(check_user_depent_of_agent($proposal->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        $requestData = $request->input();
        $lastIssue = HubIssue::orderByDesc('id')->first();
        $issueCode = $lastIssue ? (int) $lastIssue->issue_code + 1 : 1;
        $totalQuantity = 0;
        $productData = json_decode($requestData['hiddenData'], true);
        $warehouseHub = Warehouse::find($requestData['warehouse_id']);
        $proposalDetails = $proposal->proposalReceiptDetail;
        foreach ($productData as $productDT) {

            $totalQuantity += $productDT['quantity'];
        }
        $dataHubIssue = [
            'proposal_id' => $proposal->id,
            'warehouse_issue_id' => $requestData['warehouse_id'],
            'warehouse_name' => $warehouseHub->name,
            'warehouse_address' => $warehouseHub->hub->address,
            'issuer_id' => Auth::user()->id,
            'invoice_issuer_name' => Auth::user()->name,
            'warehouse_id' => $proposal->warehouse_receipt_id,
            'warehouse_type' => AgentWarehouse::class,
            'quantity' => $totalQuantity,
            'is_warehouse' => $proposal->is_warehouse,
            'title' => $proposal->title,
            'description' => $requestData['descriptionForm'],
            'expected_date' => Carbon::createFromFormat('d-m-Y', ($requestData['expectDate'])),
            'general_order_code' => $proposal->general_order_code,
            'status' => 'pending',
            'from_proposal_receipt' => 1,
            'issue_code' => $issueCode
        ];
        DB::beginTransaction();
        try {
            $proposal->status = ProposalAgentEnum::WAIT;
            $proposal->warehouse_type = Warehouse::class;
            $proposal->warehouse_id = $requestData['warehouse_id'];
            $proposal->expected_date_submit = Carbon::createFromFormat('d-m-Y', ($requestData['expectDate']));
            $proposal->save();
            $hubIssue = HubIssue::query()->create($dataHubIssue);
            foreach ($productData as $prd) {
                $product = Product::find($prd['productId']);
                $dataProductIssueDetail = [
                    'hub_issue_id' => $hubIssue->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'quantity' => $prd['quantity'],
                    'is_batch' => 1
                ];
                $productIssueDetail = HubIssueDetail::query()->create($dataProductIssueDetail);
                foreach ($proposalDetails as $proposalDetail) {
                    if ($proposalDetail->product_id == $prd['productId']) {
                        $proposalDetail->quantity_submit = $prd['quantity'];
                        $proposalDetail->save();
                    }
                }
            }

            $arrNoti = [
                'action' => 'đã tạo',
                'permission' => "hub-issue.confirm",
                'route' => route('hub-issue.view-confirm', $hubIssue->id),
                'status' => 'đã tạo'
            ];
            send_notify_cms_and_tele($hubIssue, $arrNoti);
            $arrNoti = [
                'action' => 'đã duyệt',
                'permission' => "proposal-agent-receipt.create",
                'route' => route('hub-issue.view-confirm', $hubIssue->id),
                'status' => 'đã duyệt'
            ];
            send_notify_cms_and_tele($proposal, $arrNoti);
            DB::commit();
            return $response
                ->setPreviousUrl(route('proposal-agent-receipt.index'))
                ->setNextUrl(route('proposal-agent-receipt.index'))
                ->setMessage(trans('Đã duyệt đơn đề xuất'));
        } catch (Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }
    }
    private function expectedDate($expected_date)
    {
        return Carbon::createFromFormat('Y-m-d', $expected_date);
    }

    private function getProposalProductIssue($proposal, $response)
    {
        if (!request()->user()->hasPermission('warehouse-finished-products.warehouse-all')) {
            $warehouseUsers = AgentWarehouse::where('user_id', \Auth::id())->get();
            $warehouseIds = $warehouseUsers->pluck('warehouse_id')->toArray();
            if (!in_array($proposal->warehouse_id, $warehouseIds)) {
                return $response
                    ->setError()
                    ->setMessage(__('Không có quyền truy cập! Đơn hàng đã được duyệt hoặc từ chối'));
            }
        }
        return $proposal;
    }

    public function view($id, BaseHttpResponse $response)
    {
        $proposal = ProposalAgentReceipt::where('id', $id)->with('proposalReceiptDetail')->first();
        PageTitle::setTitle('Thông tin nhập kho');
        abort_if(check_user_depent_of_agent($proposal->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        $proposal = $this->getProposalProductIssue($proposal, $response);
        $receipt = AgentReceipt::where('proposal_id', $proposal->id)->first();
        return view('plugins/agent::receipt-product.view', compact('proposal', 'receipt'));
    }

    public function getGenerateReceiptProduct(Request $request, ProposalReceiptHelper $receiptHelper)
    {
        $data = ProposalAgentReceipt::with('proposalReceiptDetail')->find($request->id);

        if ($request->button_type === 'print') {
            return $receiptHelper->streamInvoice($data);
        }
        return $receiptHelper->downloadInvoice($data);
    }
    public function denied($id, Request $request, BaseHttpResponse $response)
    {
        $proposal = ProposalAgentReceipt::find($id);

        abort_if(check_user_depent_of_agent($proposal->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        $proposal->status = ProposalAgentEnum::DENIED;
        $proposal->invoice_confirm_name = Auth::user()->name;
        $proposal->reason_cancel = $request->input('denyReason');
        $proposal->save();
        $arrNoti = [
            'action' => 'từ chối duyệt',
            'permission' => "proposal-agent-receipt.create",
            'route' => route('proposal-agent-receipt.view', $proposal->id),
            'status' => 'từ chối duyệt'
        ];
        send_notify_cms_and_tele($proposal, $arrNoti);
        return $response->setPreviousUrl(route('proposal-agent-receipt.index'))
            ->setNextUrl(route('proposal-agent-receipt.index'))->setMessage(trans('Từ chối duyệt đơn'));
    }
    public function getProductInHub($id, Request $request)
    {


        $agent = Agent::find($id);
        $hubWarehouseId = $agent->hub->warehouseInHub->first()->id;
        $serch = $request->keySearch;
        if ($serch != "") {
            $productDetail = QuantityProductInStock::where(['stock_id' => $hubWarehouseId])->where('quantity', '>', 0)
                ->whereHas(
                    'product',
                    function ($query) use ($serch) {
                        $query->where('status', 'published')
                            ->where(function ($q) use ($serch) {
                                return $q->where('name', 'LIKE', "%" . $serch . "%")->orWhere('sku', 'LIKE', "%" . $serch . "%");
                            });
                    },
                )
                ->with([
                    'product' => function ($query) {
                        $query->where('status', 'published');
                    },
                    'product.variationInfo',
                    'product.parentProduct',
                    'product.productAttribute',
                ])->limit(10)->get();
        } else {
            $productDetail = QuantityProductInStock::where(['stock_id' => $hubWarehouseId])->where('quantity', '>', 0)->with([
                'product' => function ($query) {
                    $query->where('status', 'published');
                },
                'product.variationInfo',
                'product.parentProduct',
                'product.productAttribute',
            ])->limit(10)->get();
        }

        return response()->json(['dataDetail' => $productDetail], 200);
    }
}
