<?php

namespace Botble\HubWarehouse\Http\Controllers;

use Botble\Agent\Enums\ProposalAgentEnum;
use Botble\Agent\Http\Controllers\AgentController;
use Botble\Agent\Models\AgentWarehouse;
use Botble\Agent\Models\ProposalAgentReceipt;
use Botble\Base\Facades\Assets;
use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Http\Requests\HubApproveProposalRequest;
use Botble\HubWarehouse\Http\Requests\ProposalHubIssueRequest;
use Botble\HubWarehouse\Models\HubIssue;
use Botble\HubWarehouse\Models\HubIssueDetail;
use Botble\HubWarehouse\Models\HubUser;
use Botble\HubWarehouse\Models\ProposalHubIssue;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\HubWarehouse\Models\ProposalHubIssueDetail;
use Botble\HubWarehouse\Models\QuantityProductInStock;
use Botble\HubWarehouse\Models\Warehouse;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\Showroom\Models\ShowroomProposalReceipt;
use Botble\Showroom\Models\ShowroomWarehouse;
use Botble\WarehouseFinishedProducts\Enums\ProductBatchStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use Botble\HubWarehouse\Tables\ProposalHubIssueTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\HubWarehouse\Forms\ProposalHubIssueForm;
use Botble\Base\Forms\FormBuilder;
use Botble\HubWarehouse\Models\HubWarehouse;
use Botble\HubWarehouse\Supports\ProposalHubIssueHelper;
use Botble\SaleWarehouse\Models\SaleWarehouse;
use Botble\SaleWarehouse\Models\SaleWarehouseChild;
use Botble\WarehouseFinishedProducts\Enums\ProposalIssueStatusEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProposalHubIssueController extends BaseController
{
    // private $preXK = 'XK';
    // private $preNK = 'NK';
    private function expectedDate($expected_date)
    {
        return Carbon::createFromFormat('Y-m-d', $expected_date);
    }
    private function totalQuantity($quantityBatch)
    {
        $quantity = 0;
        $totalAmount = 0;

        foreach ($quantityBatch as $key => $value) {
            $product = Product::find($key);
            $quantity += $value['quantity'];
            $totalAmount += $value['quantity'] * $product->price;
        }
        return compact('quantity', 'totalAmount');
    }
    public function index(ProposalHubIssueTable $table)
    {
        PageTitle::setTitle(trans('Danh sách'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('Tạo mới đơn xuất kho'));

        return $formBuilder->create(ProposalHubIssueForm::class)->renderForm();
    }

    public function store(ProposalHubIssueRequest $request, BaseHttpResponse $response)
    {
        $requestData = $request->input();
        $expectedDate = $this->expectedDate($requestData['expected_date']);
        $warehouse = Warehouse::where('id', $requestData['warehouse_issue_id'])->first();
        $totals = 0;
        foreach ($requestData['listProduct'] as $list) {
            if (isset($list['quantity'])) {
                $totals += $list['quantity'];
            }
        }
        if ($requestData['is_warehouse'] == 0) {
            $warehouse_id = $requestData['warehouseAgent'];
            $warehouse_type = AgentWarehouse::class;
        } else if ($requestData['is_warehouse'] == 1) {
            $warehouse_id = $requestData['hub'];
            $warehouse_type = Warehouse::class;
        } else if ($requestData['is_warehouse'] == 2) {
            $warehouse_id = $requestData['warehouse_out'];
            $warehouse_type = Warehouse::class;
        } else if ($requestData['is_warehouse'] == 3) {
            $warehouse_id = $requestData['warehouse_product'];
            $warehouse_type = WarehouseFinishedProducts::class;
        } else if ($requestData['is_warehouse'] == 4) {
            $warehouse_id = $requestData['warehouseShowroom'];
            $warehouse_type = ShowroomWarehouse::class;
        } else if ($requestData['is_warehouse'] == 5) {
            if ($warehouse?->hub?->saleWarehouse?->warehouseChild?->first()) {
                $warehouse_id = $warehouse?->hub?->saleWarehouse?->warehouseChild?->first()->id;
                $warehouse_type = SaleWarehouseChild::class;
            } else {
                return $response
                    ->setError()
                    ->setMessage('Trong hub chưa có kho Sale');
            }
        } else if ($requestData['is_warehouse'] == 6) {
            $warehouse_id = null;
            $warehouse_type = null;
        } else {
            $warehouse_id = $requestData['warehouseShowroom'];
            $warehouse_type = ShowroomWarehouse::class;
        }

        $lastProposalHubIssue = ProposalHubIssue::orderByDesc('id')->first();
        $proposalCode = $lastProposalHubIssue ? (int) $lastProposalHubIssue->proposal_code + 1 : 1;
        $dataCreate = [
            'warehouse_issue_id' => $requestData['warehouse_issue_id'],
            'warehouse_name' => $warehouse->name,
            'title' => $requestData['title'],
            'warehouse_address' => $warehouse->hub->address,
            'issuer_id' => Auth::user()->id,
            'invoice_issuer_name' => Auth::user()->name,
            'warehouse_id' => $warehouse_id,
            'warehouse_type' => $warehouse_type,
            'general_order_code' => $requestData['general_order_code'],
            'description' => $requestData['description'],
            'expected_date' => $expectedDate,
            'status' => 'pending',
            'is_warehouse' => $requestData['is_warehouse'],
            'quantity' => $totals,
            'proposal_code' => $proposalCode,
            'policies_id' => $requestData['is_warehouse'] == 5 ? $requestData['policy_sale'] : null,
        ];
        DB::beginTransaction();
        try {
            $proposalHubIssue = ProposalHubIssue::query()->create($dataCreate);
            foreach ($requestData['listProduct'] as $key => $value) {
                $product = Product::find($key);
                $color = '';
                $size = '';

                foreach ($product->variationProductAttributes as $attribute) {
                    if ($attribute->color) {
                        $color = $attribute->title;
                    } else {
                        $size = $attribute->title;
                    }
                }
                $data = [
                    'proposal_id' => $proposalHubIssue->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'color' => $color,
                    'size' => $size,
                    'price' => $product->price,
                    'sku' => $product->sku,
                    'quantity' => (int) $value['quantity'],
                    'is_batch' => (int) 0,
                ];
                ProposalHubIssueDetail::query()->create($data);
            }
            DB::commit();
            $arrNoti = [
                'action' => 'tạo',
                'permission' => "proposal-hub-issue.approve",
                'route' => route('proposal-hub-issue.approveProposalProductIssue', $proposalHubIssue->id),
                'status' => 'tạo'
            ];
            send_notify_cms_and_tele($proposalHubIssue, $arrNoti);
            event(new CreatedContentEvent(PROPOSAL_HUB_ISSUE_MODULE_SCREEN_NAME, $request, $proposalHubIssue));
            return $response
                ->setPreviousUrl(route('proposal-hub-issue.index'))
                ->setNextUrl(route('proposal-hub-issue.edit', $proposalHubIssue->getKey()))
                ->setMessage(trans('core/base::notices.create_success_message'));
        } catch (Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }
    }

    public function edit(ProposalHubIssue $proposalHubIssue, FormBuilder $formBuilder, BaseHttpResponse $response)
    {
        PageTitle::setTitle(trans('Chỉnh sửa đề xuất :name', ['name' => $proposalHubIssue->proposal_code]));

        abort_if(check_user_depent_of_hub($proposalHubIssue->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        if (
            $proposalHubIssue->issuer_id == Auth::user()->id && $proposalHubIssue->status->toValue() != ProposalProductEnum::APPOROVED
            && $proposalHubIssue->status->toValue() != ProposalProductEnum::CONFIRM
            && $proposalHubIssue->status->toValue() != ProposalProductEnum::REFUSE && !$proposalHubIssue->proposal_receipt_id
        ) {

            return $formBuilder->create(ProposalHubIssueForm::class, ['model' => $proposalHubIssue])->renderForm();
        } else {
            $errorMessage = __('Không thể truy cập để sửa đơn này!');

            if ($proposalHubIssue->issuer_id != Auth::user()->id) {
                $errorMessage = __('Người đề xuất không phải là bạn.');
            }

            if (
                in_array($proposalHubIssue->status->toValue(), [
                    ProposalProductEnum::APPOROVED,
                    ProposalProductEnum::CONFIRM,
                    ProposalProductEnum::REFUSE
                ])
            ) {
                $errorMessage = __('Trạng thái đề xuất không cho phép chỉnh sửa.');
            }

            if ($proposalHubIssue->proposal_receipt_id) {
                $errorMessage = __('Đã có phiếu nhập hàng liên kết với đề xuất này.');
            }

            return $response
                ->setError()
                ->setMessage($errorMessage);
        }
    }

    public function update(ProposalHubIssue $proposalHubIssue, ProposalHubIssueRequest $request, BaseHttpResponse $response)
    {
        if ($proposalHubIssue->status != ProposalIssueStatusEnum::PENDING) {
            return $response
                ->setError()
                ->setMessage('Đơn không thể thay đổi');
        }
        DB::beginTransaction();
        try {
            $requestData = $request->input();
            $expectedDate = $this->expectedDate($requestData['expected_date']);
            $totals = 0;
            foreach ($requestData['listProduct'] as $list) {
                if (isset($list['quantity'])) {
                    $totals += $list['quantity'];
                }
            }
            $warehouse = Warehouse::where('id', $requestData['warehouse_issue_id'])->first();
            if ($requestData['is_warehouse'] == 0) {
                $warehouse_id = $requestData['warehouseAgent'];
                $warehouse_type = AgentWarehouse::class;
            } else if ($requestData['is_warehouse'] == 3) {
                $warehouse_id = $requestData['warehouse_product'];
                $warehouse_type = WarehouseFinishedProducts::class;
            } else if ($requestData['is_warehouse'] == 1) {
                $warehouse_id = $requestData['warehouseHub'];
                $warehouse_type = Warehouse::class;
            } else if ($requestData['is_warehouse'] == 2) {
                $warehouse_id = $requestData['warehouse_out'];
                $warehouse_type = Warehouse::class;
            } else if ($requestData['is_warehouse'] == 5) {
                if ($warehouse?->hub?->saleWarehouse?->warehouseChild?->first()) {
                    $warehouse_id = $warehouse?->hub?->saleWarehouse?->warehouseChild?->first()->id;
                    $warehouse_type = SaleWarehouseChild::class;
                } else {
                    return $response
                        ->setError()
                        ->setMessage('Trong hub chưa có kho Sale');
                }
            } else if ($requestData['is_warehouse'] == 6) {
                $warehouse_id = null;
                $warehouse_type = null;
            } else if ($requestData['is_warehouse'] == 4) {
                $warehouse_id = $requestData['warehouseShowroom'];
                $warehouse_type = ShowroomWarehouse::class;
            }
            $dataUpdate = [
                'warehouse_issue_id' => $requestData['warehouse_issue_id'],
                'title' => $requestData['title'],
                'warehouse_name' => $warehouse->name,
                'issuer_id' => Auth::user()->id,
                'invoice_issuer_name' => Auth::user()->name,
                'warehouse_id' => $warehouse_id,
                'warehouse_type' => $warehouse_type,
                'general_order_code' => $requestData['general_order_code'],
                'description' => $requestData['description'],
                'expected_date' => $expectedDate,
                'status' => 'pending',
                'is_warehouse' => $requestData['is_warehouse'],
                'quantity' => $totals,
                'policies_id' => $requestData['is_warehouse'] == 5 ? $requestData['policy_sale'] : null,

            ];
            $proposalHubIssue->update($dataUpdate);
            ProposalHubIssueDetail::where('proposal_id', $proposalHubIssue->id)->delete();
            foreach ($requestData['listProduct'] as $key => $value) {
                $product = Product::find($key);
                $color = '';
                $size = '';

                foreach ($product->variationProductAttributes as $attribute) {
                    if ($attribute->color) {
                        $color = $attribute->title;
                    } else {
                        $size = $attribute->title;
                    }
                }
                $data = [
                    'proposal_id' => $proposalHubIssue->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'color' => $color,
                    'size' => $size,
                    'price' => $product->price,
                    'sku' => $product->sku,
                    'quantity' => (int) $value['quantity'],
                    'is_batch' => (int) 0,
                ];
                ProposalHubIssueDetail::query()->create($data);
            }
            $arrNoti = [
                'action' => 'chỉnh sửa',
                'permission' => "proposal-hub-issue.approve",
                'route' => route('proposal-hub-issue.approveProposalProductIssue', $proposalHubIssue->id),
                'status' => 'chỉnh sửa'
            ];
            send_notify_cms_and_tele($proposalHubIssue, $arrNoti);
            DB::commit();
            event(new UpdatedContentEvent(PROPOSAL_HUB_ISSUE_MODULE_SCREEN_NAME, $request, $proposalHubIssue));

            return $response
                ->setPreviousUrl(route('proposal-hub-issue.index'))
                ->setMessage(trans('core/base::notices.update_success_message'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }

    }

    public function destroy(ProposalHubIssue $proposalHubIssue, Request $request, BaseHttpResponse $response)
    {
        DB::beginTransaction();
        try {
            $proposalDetails = $proposalHubIssue->proposalHubIssueDetail;
            foreach ($proposalDetails as $proposalDetail) {
                $proposalDetail->delete();
            }
            $proposalHubIssue->delete();
            DB::commit();

            event(new DeletedContentEvent(PROPOSAL_HUB_ISSUE_MODULE_SCREEN_NAME, $request, $proposalHubIssue));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            DB::rollBack();

            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
    public function getProductProposal($proposal_id)
    {
        $proposal = ProposalHubIssue::find($proposal_id);
        $product = ProposalHubIssueDetail::where('proposal_id', $proposal_id)->with([
            'product',
            'product.parentProduct',
            'batch',
            'batch.listProduct',
            'productStock' => function ($q) use ($proposal) {
                $q->where('stock_id', $proposal->warehouse_issue_id);
            }
        ])->get();
        $idHubIssue = $proposal->warehouseIssue->hub->id;
        $warehouseReceipt = $proposal->warehouse;
        return response()->json([
            'data' => $product,
            'proposal' => $proposal,
            'hubIssue' => $idHubIssue,
            'warehouseReceipt' => $warehouseReceipt
        ], 200);
    }
    public function approveProposalProductIssue(int|string $id, BaseHttpResponse $response)
    {
        $authUserId = Auth::user();
        Assets::addScriptsDirectly([
            'vendor/core/plugins/gallery/js/gallery-admin.js',
            'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
            'vendor/core/plugins/ecommerce/js/order.js',
            'vendor/core/plugins/hub-warehouse/js/approve-product-issue.js',
        ])
            ->addScripts(['blockui', 'input-mask']);
        $hubUsers = HubUser::where('user_id', \Auth::id())->get();
        $proposal = null;
        $proposal = $this->getProposalHubIssue($id, $hubUsers);

        if ($proposal->status != ProposalProductEnum::PENDING) {
            return $response
                ->setError()
                ->setMessage(__('Đơn hàng đã duyệt hoặc từ chối không có quyền truy cập!!'));
        }
        // dd($proposal->proposalProductIssueDetail);
        PageTitle::setTitle(__('Duyệt đơn đề xuất xuất kho HUB'));
        return view('plugins/hub-warehouse::proposal-issue.approve', compact('proposal'));
    }
    function getProposalHubIssue($id, $hubUsers)
    {
        // Check if the authenticated user has the required permission
        if (\Auth::user()->hasPermission('proposal-hub-issue.approve')) {
            // If they have the permission, get the proposal product issue based on the provided ID
            $proposal = ProposalHubIssue::where(['id' => $id])
                ->with('proposalHubIssueDetail')->first();
        } else {
            // If they don't have the permission, loop through the warehouse users
            foreach ($hubUsers as $hubuser) {
                $proposal = ProposalHubIssue::where(['id' => $id])
                    ->with('proposalHubIssueDetail')
                    ->where('warehouse_id', $hubuser->warehouse_id)
                    ->first();
                // Break the loop if a proposal is found
                if ($proposal) {
                    break;
                }
            }
        }
        // If no proposal is found, abort with a 403 error
        if (!$proposal) {
            abort(403, 'Bạn không có quyền xem đơn này!!!!');
        }

        // Return the found proposal
        return $proposal;
    }
    public function findProposalAgentOrShowroom($proposal)
    {
        if ($proposal->proposal_receipt_id) {
            $receiptClass = null;
            if ($proposal->warehouse_type == ShowroomWarehouse::class) {
                $receiptClass = ShowroomProposalReceipt::class;
            } elseif ($proposal->warehouse_type == AgentWarehouse::class) {
                $receiptClass = ProposalAgentReceipt::class;
            }
            if ($receiptClass) {
                return $receiptClass::find($proposal->proposal_receipt_id);
            }
        }

        return null;
    }
    public function approve(int|string $id, HubApproveProposalRequest $request, BaseHttpResponse $response)
    {
        $requestData = $request->input();
        $expectedDate = Carbon::createFromFormat('d-m-Y', $requestData['expected_date']);
        DB::beginTransaction();
        try {
            $proposal = ProposalHubIssue::where('id', $id)->sharedLock()->first();

            abort_if(check_user_depent_of_hub($proposal->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

            $proposalReceipt = $this->findProposalAgentOrShowroom($proposal);
            if ($proposal->status != ProposalProductEnum::PENDING) {
                DB::rollBack();
                return $response
                    ->setError()
                    ->setMessage(__('Đơn hàng đã duyệt hoặc từ chối !'));

            }
            if ($proposalReceipt) {
                $proposalReceipt->update([
                    'expected_date_submit' => $expectedDate,
                    'date_confirm' => Carbon::now()->format('Y-m-d'),
                    'invoice_confirm_name' => Auth::user()->name,
                    'status' => ProposalAgentEnum::WAIT,
                ]);
            }
            $proposal->update([
                'status' => ProposalProductEnum::APPOROVED,
                'invoice_confirm_name' => Auth::user()->name,
                'date_confirm' => Carbon::now()->format('Y-m-d'),
            ]);
            $totalQuantity = 0;
            if (isset($requestData['product'])) {

                foreach ($requestData['product'] as $key => $product) {
                    $totalQuantity += ((int) $product['quantity']);
                }
            }

            if (isset($requestData['batch'])) {

                foreach ($requestData['batch'] as $key => $batch) {
                    $totalQuantity += ((int) $batch['quantity']);
                }
            }
            $lastIssue = HubIssue::orderByDesc('id')->first();
            $issueCode = $lastIssue ? (int) $lastIssue->issue_code + 1 : 1;
            if ($proposal->proposal_receipt_id) {
                $from_proposal_receipt = 1;
            } else {
                $from_proposal_receipt = 0;
            }
            $dataHubIssue = [
                'proposal_id' => $proposal->id,
                'warehouse_issue_id' => $proposal->warehouse_issue_id,
                'warehouse_name' => $proposal->warehouse_name,
                'warehouse_address' => $proposal->warehouse_address,
                'issuer_id' => Auth::user()->id,
                'invoice_issuer_name' => Auth::user()->name,
                'warehouse_id' => $proposal->warehouse_id,
                'warehouse_type' => $proposal->warehouse_type,
                'quantity' => $totalQuantity,
                'is_warehouse' => $proposal->is_warehouse,
                'title' => $proposal->title,
                'description' => $requestData['description'],
                'expected_date' => $expectedDate,
                'general_order_code' => $proposal->general_order_code,
                'proposal_code' => $proposal->proposal_code,
                'status' => 'pending',
                'from_proposal_receipt' => $from_proposal_receipt,
                'issue_code' => $issueCode,
            ];
            $hubIssue = HubIssue::query()->create($dataHubIssue);
            if (isset($requestData['batch'])) {
                foreach ($requestData['batch'] as $key => $batchs) {
                    $product = Product::find($batchs['product_id']);
                    $dataProductIssueDetail = [
                        'hub_issue_id' => $hubIssue->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'sku' => $product->sku,
                        'price' => $product->price,
                        'quantity' => $batchs['quantity'],
                        'is_batch' => 1,
                        'batch_id' => $key
                    ];
                    HubIssueDetail::query()->create($dataProductIssueDetail);
                }
            }
            if (isset($requestData['product'])) {
                foreach ($requestData['product'] as $key => $products) {
                    $product = Product::find($key);

                    $quantityInStock = QuantityProductInStock::where(['product_id' => $key, 'stock_id' => $proposal->warehouse_issue_id])->first();
                    if ($products['quantity'] > $quantityInStock) {
                        return $response
                            ->setError()
                            ->setMessage('Số lượng sản phẩm ' . $products->name . ' không còn đủ trong kho!!');
                    }
                    if ($proposalReceipt) {
                        foreach ($proposalReceipt->proposalReceiptDetail as $proposalDetail) {
                            if ($proposalDetail->product_id == $key) {
                                $proposalDetail->quantity_submit = (int) $products['quantity'];
                                $proposalDetail->save();
                            }
                        }
                    }
                    $dataProductIssueDetail = [
                        'hub_issue_id' => $hubIssue->id,
                        'product_id' => $key,
                        'product_name' => $product->name,
                        'sku' => $product->sku,
                        'price' => $product->price,
                        'quantity' => $products['quantity'],
                        'color' => $products['color'],
                        'size' => $products['size'],
                        'is_batch' => 0
                    ];
                    HubIssueDetail::query()->create($dataProductIssueDetail);
                }
            }
            $arrNoti = [
                'action' => 'tạo',
                'permission' => "product-issue.confirm",
                'route' => route('hub-issue.view-confirm', $hubIssue->id),
                'status' => 'tạo'
            ];
            send_notify_cms_and_tele($hubIssue, $arrNoti);
            //Notify for creator proposal
            $arrNoti = [
                'action' => 'duyệt',
                'permission' => "proposal-hub-issue.create",
                'route' => route('proposal-hub-issue.view', $proposal->id),
                'status' => 'duyệt'
            ];

            send_notify_cms_and_tele($proposal, $arrNoti);
            DB::commit();
            return $response
                ->setPreviousUrl(route('proposal-hub-issue.index'))
                ->setNextUrl(route('proposal-hub-issue.index'))
                ->setMessage(trans('core/base::notices.update_success_message'));

        } catch (Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }
    }
    public function denied(int|string $id, Request $request, BaseHttpResponse $response)
    {
        $proposalHubIssue = ProposalHubIssue::findOrFail($id);

        abort_if(check_user_depent_of_hub($proposalHubIssue->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        DB::beginTransaction();
        try {
            $proposalHubIssue->update(['status' => 'denied', 'reason_cancel' => $request->input('denyReason'), 'invoice_confirm_name' => Auth::user()->name]);
            $proposalReceipt = $this->findProposalAgentOrShowroom($proposalHubIssue);
            if ($proposalReceipt) {
                $proposalReceipt->status = ProposalAgentEnum::DENIED;
                $proposalReceipt->invoice_confirm_name = Auth::user()->name;
                $proposalReceipt->reason_cancel = $request->input('denyReason');
                $proposalReceipt->save();
                if ($proposalHubIssue->warehouse_type == ShowroomWarehouse::class) {
                    $arrNoti = [
                        'action' => 'từ chối',
                        'permission' => "proposal-showroom-receipt.create",
                        'route' => route('proposal-showroom-receipt.view', $proposalReceipt->id),
                        'status' => 'từ chối'
                    ];
                    send_notify_cms_and_tele($proposalReceipt, $arrNoti);
                } else if ($proposalHubIssue->warehouse_type == AgentWarehouse::class) {
                    $arrNoti = [
                        'action' => 'từ chối',
                        'permission' => "proposal-agent-receipt.create",
                        'route' => route('proposal-agent-receipt.view', $proposalReceipt->id),
                        'status' => 'từ chối'
                    ];
                    send_notify_cms_and_tele($proposalReceipt, $arrNoti);
                }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }
        $arrNoti = [
            'action' => 'từ chối',
            'permission' => "proposal-hub-issue.create",
            'route' => route('proposal-hub-issue.view', $proposalHubIssue->id),
            'status' => 'từ chối'
        ];
        send_notify_cms_and_tele($proposalHubIssue, $arrNoti);
        return $response
            ->setPreviousUrl(route('proposal-hub-issue.index'))
            ->setNextUrl(route('proposal-hub-issue.index'))
            ->setError()
            ->setMessage('Từ chối đơn đề xuất kho');
    }
    public function view(int|string $id)
    {
        $proposal = ProposalHubIssue::where(['id' => $id])
            ->with('proposalHubIssueDetail')->first();

        abort_if(check_user_depent_of_hub($proposal->warehouse_issue_id), 403, 'Bạn không có quyền xem đơn này!!!!');

        if ($proposal->proposal_receipt_id) {
            $receipt = HubIssue::where(['proposal_id' => $id, 'from_proposal_receipt' => 1])->with('productIssueDetail')->first();
        } else {
            $receipt = HubIssue::where(['proposal_id' => $id, 'from_proposal_receipt' => 0])->with('productIssueDetail')->first();
        }
        return view('plugins/hub-warehouse::proposal-issue/view', compact('proposal', 'receipt'));
    }


    public function getGenerateReceiptProduct(Request $request, ProposalHubIssueHelper $hubReceiptHelper)
    {
        $data = ProposalHubIssue::with('proposalHubIssueDetail')->find($request->id);

        if ($request->button_type === 'print') {
            return $hubReceiptHelper->streamInvoice($data);
        }
        return $hubReceiptHelper->downloadInvoice($data);
    }
}
