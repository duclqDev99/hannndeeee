<?php

namespace Botble\WarehouseFinishedProducts\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Models\ProposalHubReceipt;
use Botble\HubWarehouse\Models\ProposalHubReceiptDetail;
use Botble\HubWarehouse\Models\Warehouse;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProductBatchStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProposalIssueStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;
use Botble\WarehouseFinishedProducts\Http\Requests\ProposalProductIssueRequest;
use Botble\WarehouseFinishedProducts\Http\Resources\ProposalProductIssueDetailsResource;
use Botble\WarehouseFinishedProducts\Models\Hub;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Botble\WarehouseFinishedProducts\Models\ProductIssue;
use Botble\WarehouseFinishedProducts\Models\ProductIssueDetails;
use Botble\WarehouseFinishedProducts\Models\ProposalProductIssue;
use Botble\WarehouseFinishedProducts\Models\ProposalProductIssueDetails;
use Botble\WarehouseFinishedProducts\Models\ProposalReceiptProductDetails;
use Botble\WarehouseFinishedProducts\Models\ProposalReceiptProducts;
use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;
use Botble\WarehouseFinishedProducts\Models\WarehouseUser;
use Botble\WarehouseFinishedProducts\Supports\ProductIssueHelper;
use Botble\WarehouseFinishedProducts\Supports\ProposalProductIssueHelper;
use Illuminate\Http\Request;
use Exception;
use Botble\WarehouseFinishedProducts\Tables\ProposalProductIssueTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\WarehouseFinishedProducts\Forms\ProposalProductIssueForm;
use Botble\Base\Forms\FormBuilder;
use Botble\WarehouseFinishedProducts\Models\QuantityProductInStock;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProposalProductIssueController extends BaseController
{

    private function expectedDate($expected_date)
    {
        return Carbon::createFromFormat('Y-m-d', $expected_date);
    }

    // private $preXK = 'XK';
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
    public function index(ProposalProductIssueTable $table)
    {
        PageTitle::setTitle('Danh sách đơn đề xuất thành phẩm');
        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        Assets::addScriptsDirectly(
            [
                'https://unpkg.com/vue-multiselect@2.1.0',

            ]
        )
            ->addStylesDirectly([
                'vendor/core/plugins/warehouse-finished-products/css/form-proposal.css',
            ]);

        PageTitle::setTitle(trans('Tạo mới đơn xuất kho'));

        return $formBuilder->create(ProposalProductIssueForm::class)->renderForm();
    }
    public function edit(ProposalProductIssue $proposalProductIssue, FormBuilder $formBuilder, BaseHttpResponse $response)
    {
        if (
            $proposalProductIssue->issuer_id == Auth::user()->id && ($proposalProductIssue->status->toValue() === ProposalIssueStatusEnum::PENDING ||
                $proposalProductIssue->status->toValue() === ProposalIssueStatusEnum::DENIED)
        ) {
            Assets::addScriptsDirectly(
                [
                ]
            )->addStylesDirectly([
                        'vendor/core/plugins/warehouse-finished-products/css/form-proposal.css',
                    ]);
            PageTitle::setTitle(trans('Chỉnh sửa đơn đề xuất :name', ['name' => $proposalProductIssue->proposal_code]));
            return $formBuilder->create(ProposalProductIssueForm::class, ['model' => $proposalProductIssue])->renderForm();

        } else {
            return $response
            ->setError()
            ->setMessage(__('Không thể truy cập đơn hàng!'));
        }
    }

    public function store(ProposalProductIssueRequest $request, BaseHttpResponse $response)
    {
        $requestData = $request->input();
        $expectedDate = $this->expectedDate($requestData['expected_date']);
        $warehouse = WarehouseFinishedProducts::where('id', $requestData['warehouse_id'])->first();
        $totals = $this->totalQuantity($requestData['product']);
        if ($requestData['is_warehouse'] == 1) {
            $warehouse_receipt_id = $requestData['warehouse_out'];
            $warehouse_type = WarehouseFinishedProducts::class;
        } else if ($requestData['is_warehouse'] == 0) {
            $warehouse_receipt_id = $requestData['stock_warehouse'];
            $warehouse_type = Warehouse::class;
        } else {
            $warehouse_receipt_id = 0;
            $warehouse_type = '';
        }
        // $lastProposal = ProposalProductIssue::orderByDesc('id')->first();
        // $proposalCode = $lastProposal ? (int) $lastProposal->proposal_code + 1 : 1;
        $lastProposal = ProposalProductIssue::orderByDesc('id')->first();
        $proposalCode = $lastProposal ? (int) $lastProposal->proposal_code + 1 : 1;
        $dataCreate = [
            'warehouse_id' => $requestData['warehouse_id'],
            'warehouse_issue_type' => WarehouseFinishedProducts::class,
            'warehouse_name' => $warehouse->name,
            'title' => $requestData['title'],
            'issuer_id' => Auth::user()->id,
            'invoice_issuer_name' => Auth::user()->name,
            'warehouse_receipt_id' => $warehouse_receipt_id,
            'warehouse_type' => $warehouse_type,
            'general_order_code' => $requestData['general_order_code'],
            'description' => $requestData['description'],
            'expected_date' => $expectedDate,
            'status' => 'pending',
            'is_warehouse' => $requestData['is_warehouse'],
            'quantity' => $totals['quantity'],
            'proposal_code' => $proposalCode,
            'is_batch' => 0,
        ];
        DB::beginTransaction();
        try {
            $proposalProductIssue = ProposalProductIssue::query()->create($dataCreate);


            foreach ($requestData['product'] as $key => $value) {
                $product = Product::find($key);
                $color = '';
                $size = '';

                $arrAttribute = $product->variationProductAttributes;
                if (count($arrAttribute) > 0) {
                    if (count($arrAttribute) === 1) {
                        $color = $arrAttribute[0]->color == null ? '' : $arrAttribute[0]->title;
                        $size = $arrAttribute[0]->color == null ? $arrAttribute[0]->title : '';
                    } else if (count($arrAttribute) === 2) {
                        $color = $arrAttribute[0]->color == null ? $arrAttribute[1]->title : $arrAttribute[0]->title;
                        $size = $arrAttribute[0]->color == null ? $arrAttribute[0]->title : $arrAttribute[1]->title;
                    }
                }
                $data = [
                    'proposal_product_issue_id' => $proposalProductIssue->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'color' => $color,
                    'size' => $size,
                    'price' => $product->price,
                    'sku' => $product->sku,
                    'quantity' => (int) $value['quantity'],
                ];
                $proposalProductIssueDetail = ProposalProductIssueDetails::query()->create($data);
            }
            $arrNoti = [
                'action' => 'tạo',
                'permission' => "proposal-product-issue.examine",
                'route' => route('proposal-product-issue.approveProposalProductIssue', $proposalProductIssue->id),
                'status' => 'chờ duyệt'
            ];
            send_notify_cms_and_tele($proposalProductIssue, $arrNoti);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }

        event(new CreatedContentEvent(PROPOSAL_GOOD_RECEIPTS_MODULE_SCREEN_NAME, $request, $proposalProductIssue));

        return $response
            ->setNextUrl(route('proposal-product-issue.index'))
            ->setPreviousUrl(route('proposal-product-issue.edit', $proposalProductIssue->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }
    public function update(ProposalProductIssue $proposalProductIssue, ProposalProductIssueRequest $request, BaseHttpResponse $response)
    {
        DB::beginTransaction();
        try {
            $requestData = $request->input();
            $expectedDate = $this->expectedDate($requestData['expected_date']);
            $totals = $this->totalQuantity($requestData['product']);
            $warehouse = WarehouseFinishedProducts::where('id', $requestData['warehouse_id'])->first();
            if ($requestData['is_warehouse'] == 1) {
                $warehouseReceiptId = $requestData['warehouse_out'];
                $warehouseType = WarehouseFinishedProducts::class;
            } else if ($requestData['is_warehouse'] == 0) {
                $warehouseReceiptId = $requestData['stock_warehouse'];
                $warehouseType = Warehouse::class;
            } else {
                $warehouseReceiptId = 0;
                $warehouseType = '';
            }

            $dataUpdate = [
                'warehouse_id' => $requestData['warehouse_id'],
                'warehouse_issue_type' => WarehouseFinishedProducts::class,
                'title' => $requestData['title'],
                'warehouse_name' => $warehouse->name,
                'issuer_id' => Auth::user()->id,
                'invoice_issuer_name' => Auth::user()->name,
                'warehouse_receipt_id' => $warehouseReceiptId,
                'warehouse_type' => $warehouseType,
                'general_order_code' => $requestData['general_order_code'],
                'description' => $requestData['description'],
                'expected_date' => $expectedDate,
                'status' => 'pending',
                'is_warehouse' => $requestData['is_warehouse'],
                'quantity' => $totals['quantity'],
                // 'is_odd' => $requestData['is_odd'],
                'is_batch' => 0,
            ];

            $proposalProductIssue->fill($dataUpdate);
            $proposalProductIssue->save();
            ProposalProductIssueDetails::where('proposal_product_issue_id', $proposalProductIssue->id)->delete();

            $warehouse = WarehouseFinishedProducts::where('id', $requestData['warehouse_id'])->first();

            foreach ($requestData['product'] as $key => $value) {
                $product = Product::find($key);
                $color = '';
                $size = '';

                $arrAttribute = $product->variationProductAttributes;
                if (count($arrAttribute) > 0) {
                    if (count($arrAttribute) === 1) {
                        $color = $arrAttribute[0]->color == null ? '' : $arrAttribute[0]->title;
                        $size = $arrAttribute[0]->color == null ? $arrAttribute[0]->title : '';
                    } else if (count($arrAttribute) === 2) {
                        $color = $arrAttribute[0]->color == null ? $arrAttribute[1]->title : $arrAttribute[0]->title;
                        $size = $arrAttribute[0]->color == null ? $arrAttribute[0]->title : $arrAttribute[1]->title;
                    }
                }
                $data = [
                    'proposal_product_issue_id' => $proposalProductIssue->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'color' => $color,
                    'size' => $size,
                    'price' => $product->price,
                    'sku' => $product->sku,
                    'quantity' => (int) $value['quantity'],
                ];
                ProposalProductIssueDetails::query()->create($data);
            }
            $arrNoti = [
                'action' => 'chỉnh sửa',
                'permission' => "proposal-product-issue.approve",
                'route' => route('proposal-product-issue.approveProposalProductIssue', $proposalProductIssue->id),
                'status' => 'chỉnh sửa'
            ];
            send_notify_cms_and_tele($proposalProductIssue, $arrNoti);
            DB::commit();
            event(new UpdatedContentEvent(PROPOSAL_GOOD_RECEIPTS_MODULE_SCREEN_NAME, $request, $proposalProductIssue));

            return $response
                ->setNextUrl(route('proposal-product-issue.index'))
                ->setPreviousUrl(route('proposal-product-issue.edit', $proposalProductIssue->getKey()))
                ->setMessage(trans('Cập nhật thành công'));
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }



    }

    public function destroy(ProposalProductIssue $proposalProductIssue, Request $request, BaseHttpResponse $response)
    {
        try {

            $proposalReceipt = ProposalReceiptProducts::where('proposal_issue_id', $proposalProductIssue->id)->first();
            if (isset($proposalReceipt)) {
                $proposalReceiptDetails = ProposalReceiptProductDetails::where('proposal_id', $proposalReceipt->id)->get();
                foreach ($proposalReceiptDetails as $proposalReceiptDetail) {
                    $proposalReceiptDetail->delete();
                }
                $proposalReceipt->delete();
            }
            $proposalProductIssueDetails = ProposalProductIssueDetails::where('proposal_product_issue_id', $proposalProductIssue->id)->get();
            foreach ($proposalProductIssueDetails as $proposalProductIssueDetail) {
                $proposalProductIssueDetail->delete();
            }

            $proposalProductIssue->delete();
            $arrNoti = [
                'action' => 'xóa',
                'permission' => "proposal-product-issue.approveProposalProductIssue",
                'route' => route('product-issue.view-confirm', $proposalProductIssue->id),
                'status' => 'xóa'
            ];
            send_notify_cms_and_tele($proposalProductIssue, $arrNoti);
            event(new DeletedContentEvent(PROPOSAL_GOOD_RECEIPTS_MODULE_SCREEN_NAME, $request, $proposalProductIssue));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
    public function approveProposalProductIssue(int|string $id, BaseHttpResponse $response)
    {
        $authUserId = Auth::user();
        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/gallery/js/gallery-admin.js',
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/ecommerce/js/order.js',
                'vendor/core/plugins/warehouse-finished-products/js/approve-product-issue.js',
            ])
            ->addScripts(['blockui', 'input-mask']);
        $warehouseUsers = WarehouseUser::where('user_id', \Auth::id())->get();
        $proposal = null;
        $proposal = $this->getProposalProductIssue($id, $warehouseUsers);

        if (($proposal->status == ProposalIssueStatusEnum::PENDING && Auth::user()->hasPermission('proposal-product-issue.examine')) || ($proposal->status == ProposalIssueStatusEnum::EXAMINE && Auth::user()->hasPermission('proposal-product-issue.approve'))) {
            PageTitle::setTitle(__('Duyệt đơn đề xuất xuất kho'));
            return view('plugins/warehouse-finished-products::proposal-product-issue.approve-issue', compact('proposal'));
        } else {
            return $response
            ->setError()
            ->setMessage(__('Không có quyền truy cập!'));
        }
        // dd($proposal->proposalProductIssueDetail);
    }
    public function storeApproveProposalProductIssue(ProposalProductIssue $proposal, Request $request, BaseHttpResponse $response)
    {
        try {
            DB::beginTransaction();
            
            $totalAmount = 0;
            $totalQuantity = 0;
            $proposalDetails = ProposalProductIssueDetails::where('proposal_product_issue_id', $proposal->id)->get();
            foreach ($proposalDetails as $proposalDetail) {
                $totalQuantity += $proposalDetail->quantityExamine;
                $totalAmount += $proposalDetail->quantityExamine;
            }
            $lastIssue = ProductIssue::orderByDesc('id')->first();
            $issueCode = $lastIssue ? (int) ($lastIssue->issue_code) + 1 : 1;


            $dataProductIssue = [
                'proposal_id' => $proposal->id,
                'warehouse_id' => $proposal->warehouse_id,
                'warehouse_issue_type' => WarehouseFinishedProducts::class,
                'warehouse_name' => $proposal->warehouse_name,
                'warehouse_address' => $proposal->warehouse_address,
                'warehouse_type' => $proposal->warehouse_type,
                'issuer_id' => Auth::user()->id,
                'invoice_issuer_name' => Auth::user()->name,
                'warehouse_receipt_id' => $proposal->warehouse_receipt_id,
                'quantity' => $totalQuantity,
                'is_warehouse' => $proposal->is_warehouse,
                'title' => $proposal->title,
                'description' => $proposal->description_examine,
                'expected_date' => $proposal->expect_date_examine,
                'general_order_code' => $proposal->general_order_code,
                'issue_code' => $issueCode,
                'status' => 'pending',
                'from_proposal_receipt' => 0,
                'is_batch' => $proposal->is_batch
            ];
            $productIssue = ProductIssue::query()->create($dataProductIssue);

            $proposal->update([
                'status' => ProposalProductEnum::APPOROVED,
                'invoice_confirm_name' => Auth::user()->name,
                'date_confirm' => Carbon::now()->format('Y-m-d'),
                'proposal_code' => $issueCode
            ]);

            foreach ($proposalDetails as $proposalDetail) {
                $product = Product::find($proposalDetail->product_id);
                $quantityInStock = $product->is_variation == 0
                    ? ProductBatch::where([
                        'product_parent_id' => $product->id,
                        'warehouse_id' => $proposal->warehouse_id,
                        'warehouse_type' => WarehouseFinishedProducts::class,
                        'status' => ProductBatchStatusEnum::INSTOCK
                    ])
                        ->count()
                    : QuantityProductInStock::where([
                        'product_id' => $product->id,
                        'stock_id' => $proposal->warehouse_id
                    ])?->first()?->quantity;
                if ($proposalDetail->quantityExamine > $quantityInStock) {
                    return $response
                        ->setError()
                        ->setMessage('Số lượng sản phẩm ' . $product->name . ' không còn đủ trong kho!!');
                }
                $isBatch = empty($proposalDetail->color) && empty($proposalDetail->size) ? 1 : 0;
                $dataProductIssueDetail = [
                    'product_issue_id' => $productIssue->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'quantity' => $proposalDetail->quantityExamine,
                    'color' => $proposalDetail->color,
                    'size' => $proposalDetail->size,
                    'is_batch' => $isBatch
                ];
                $productIssueDetail = ProductIssueDetails::query()->create($dataProductIssueDetail);
            }
            //Notify for stock
            $arrNoti = [
                'action' => 'tạo phiếu',
                'permission' => "product-issue.confirm",
                'route' => route('product-issue.view-confirm', $productIssue->id),
                'status' => 'tạo phiếu'
            ];
            send_notify_cms_and_tele($productIssue, $arrNoti);
            //Notify for creator proposal
            $arrNoti = [
                'action' => 'chuyển đề xuất đến kho',
                'permission' => "proposal-product-issue.examine",
                'route' => route('proposal-product-issue.view', $proposal->id),
                'status' => 'chuyển đề xuất đến kho'
            ];

            send_notify_cms_and_tele($proposal, $arrNoti);
            DB::commit();
            return $response
                ->setPreviousUrl(route('proposal-product-issue.index'))
                ->setNextUrl(route('proposal-product-issue.index'))
                ->setMessage(trans('core/base::notices.update_success_message'));
        } catch (\Exception $e) {

            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }
    public function rejectProposalProductIssue(int|string $id, BaseHttpResponse $response, Request $request)
    {
        $proposalProductIssue = ProposalProductIssue::findOrFail($id);
        DB::beginTransaction();
        try {
            $proposalProductIssue->update(['status' => 'denied', 'reason' => $request->input('denyReason'), 'invoice_confirm_name' => Auth::user()->name]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
        $arrNoti = [
            'action' => 'từ chối',
            'permission' => "proposal-product-issue.create",
            'route' => route('proposal-product-issue.view', $proposalProductIssue->id),
            'status' => 'từ chối'
        ];
        send_notify_cms_and_tele($proposalProductIssue, $arrNoti);
        return $response
            ->setPreviousUrl(route('proposal-product-issue.index'))
            ->setNextUrl(route('proposal-product-issue.index'))
            ->setError()
            ->setMessage('Từ chối xuất sản phẩm');
    }


    public function viewProposalProductIssue(int|string $id)
    {
        $authUserId = Auth::user();
        $warehouseUsers = WarehouseUser::where('user_id', \Auth::id())->get();
        $proposal = $this->getProposalProductIssue($id, $warehouseUsers);
        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css']);
        $receipt = ProductIssue::where(['proposal_id' => $id])->with('productIssueDetail')->first();
        return view('plugins/warehouse-finished-products::proposal-product-issue.view', compact('proposal', 'receipt'));
    }

    public function getProductProposal(int|string $id)
    {
        // $proposalDetail = ProposalProductIssueDetails::where('proposal_product_issue_id', $id)->get();
        $proposal = ProposalProductIssue::find($id);
        $products = $proposal->proposalProductIssueDetail->map(function ($item) use ($proposal) {
            $item->warehouse_id = $proposal->warehouse_id; // Giả sử warehouse_id là trường bạn muốn thêm
            return $item;
        });
        $productsResource = ProposalProductIssueDetailsResource::collection($products);
        $idWarehouse = '';
        $idhub = '';

        if ($proposal->warehouse_type == Warehouse::class) {
            $idhub = $proposal->warehouse->hub->id;
            $idWarehouse = $proposal->warehouse->id;
        }
        return response()->json(['data' => $productsResource, 'proposal' => $proposal, 'idWarehouse' => $idWarehouse, 'idHub' => $idhub], 200);
    }

    public function exportProposalProductIssue($id, ProposalProductIssueHelper $proposalProductIssueHelper, Request $request)
    {
        $data = ProposalProductIssue::with('proposalProductIssueDetail')->find($id);
        $requestData = $request->input();
        if ($requestData['button_type'] === 'print') {
            return $proposalProductIssueHelper->streamInvoice($data);
        }
        return $proposalProductIssueHelper->downloadInvoice($data);
    }
    function getProposalProductIssue($id, $warehouseUsers)
    {
        // Check if the authenticated user has the required permission
        if (\Auth::user()->hasPermission('warehouse-finished-products.warehouse-all')) {
            // If they have the permission, get the proposal product issue based on the provided ID
            $proposal = ProposalProductIssue::where(['id' => $id])
                ->with('proposalProductIssueDetail')->first();
        } else {
            // If they don't have the permission, loop through the warehouse users
            foreach ($warehouseUsers as $warehouseUser) {
                $proposal = ProposalProductIssue::where(['id' => $id])
                    ->with('proposalProductIssueDetail')
                    ->where('warehouse_id', $warehouseUser->warehouse_id)
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
    public function examineProposal(ProposalProductIssue $proposal, Request $request, BaseHttpResponse $response)
    {

        $requestData = $request->input();
        $dateExamine =  Carbon::createFromFormat('d-m-Y', $requestData['expectDate']);;
        $dataProduct = json_decode($requestData['dataProduct'], true);
        DB::beginTransaction();
        try {
            $proposal->update(['status' => ProposalIssueStatusEnum::EXAMINE, 'expect_date_examine' => $dateExamine, 'description_examine' => $requestData['descriptionForm']]);
            foreach ($dataProduct as $productDT) {
                $proposalProductIssueDetail = ProposalProductIssueDetails::where(['proposal_product_issue_id' => $proposal->id, 'product_id' => $productDT['productId']])->first();
                $proposalProductIssueDetail->quantityExamine = $productDT['quantity'];
                $proposalProductIssueDetail->save();
            }
            DB::commit();
            $arrNoti = [
                'action' => 'đã duyệt',
                'permission' => "proposal-product-issue.create",
                'route' => route('proposal-product-issue.view', $proposal->id),
                'status' => 'đã duyệt'
            ];
            send_notify_cms_and_tele($proposal, $arrNoti);
            $arrNoti = [
                'action' => 'đã duyệt',
                'permission' => "proposal-product-issue.approve",
                'route' => route('proposal-product-issue.approveProposalProductIssue', $proposal->id),
                'status' => 'đã duyệt'
            ];
            send_notify_cms_and_tele($proposal, $arrNoti);
            return $response
                ->setNextUrl(route('proposal-product-issue.index'))
                ->setMessage(__('Đã duyệt đơn hàng :name', ['name' => $proposal->proposal_code]));
        } catch (Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());

        }
    }

}
