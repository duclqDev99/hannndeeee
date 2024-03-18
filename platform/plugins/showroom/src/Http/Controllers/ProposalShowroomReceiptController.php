<?php

namespace Botble\Showroom\Http\Controllers;

use Botble\Agent\Enums\ProposalAgentEnum;
use Botble\Agent\Http\Requests\ProposalAgentReceiptRequest;
use Botble\Agent\Models\ProposalAgentReceipt;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Models\HubIssue;
use Botble\HubWarehouse\Models\HubIssueDetail;
use Botble\HubWarehouse\Models\HubWarehouse;
use Botble\HubWarehouse\Models\ProposalHubIssue;
use Botble\HubWarehouse\Models\ProposalHubIssueDetail;
use Botble\HubWarehouse\Models\QuantityProductInStock;
use Botble\HubWarehouse\Models\Warehouse;
use Botble\Showroom\Models\Showroom;
use Botble\Agent\Tables\ProposalAgentReceiptTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Agent\Forms\ProposalAgentReceiptForm;
use Botble\Base\Forms\FormBuilder;
use Botble\Showroom\Forms\ProposalShowroomReceiptForm;
use Botble\Showroom\Models\ProposalShowroomReceiptDetail;
use Botble\Showroom\Models\ShowroomProposalReceipt;
use Botble\Showroom\Models\ShowroomWarehouse;
use Botble\Showroom\Supports\ProposalReceiptHelper;
use Botble\Showroom\Tables\ProposalShowroomReceiptTable;
use Botble\WarehouseFinishedProducts\Enums\ProductBatchStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Botble\WarehouseFinishedProducts\Models\ProductBatchDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProposalShowroomReceiptController extends BaseController
{
    // private $preXK = 'XK';
    // private $preNK = 'NK';
    public function index(ProposalShowroomReceiptTable $table)
    {
        PageTitle::setTitle(trans('Danh sách'));

        return $table->renderTable();
    }

    public function getWarehouse(Request $request)
    {
        $showroom_id = $request->showroom_id;
        $warehouses = ShowroomWarehouse::where('showroom_id', $showroom_id)->get();
        return response()->json([
            'success' => 1,
            'data' => $warehouses
        ], 200);
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('Tạo mới đề xuất'));

        return $formBuilder->create(ProposalShowroomReceiptForm::class)->renderForm();
    }
    function buildProposalData($requestData, $warehouseName, $warehouseAddress, $total)
    {
        return [
            'warehouse_name' => $warehouseName,
            'warehouse_address' => $warehouseAddress,
            'issuer_id' => Auth::user()->id,
            'warehouse_id' => $requestData['warehouse_receipt_id'],
            'invoice_issuer_name' => Auth::user()->name,
            'general_order_code' => $requestData['general_order_code'],
            'title' => $requestData['title'],
            'description' => $requestData['description'],
            'is_warehouse' => 4,
            'expected_date' => Carbon::createFromFormat('Y-m-d', $requestData['expected_date']),
            'quantity' => $total,
        ];
    }
    public function store(Request $request, BaseHttpResponse $response)
    {
        $requestData = $request->input();
        // tính total
        $totals = 0;
        $items = json_decode($requestData['items'], true);
        $itemOdd = json_decode($requestData['itemOdd'], true);
        foreach ($items as $item) {
            if (isset($item['quantity'])) {
                $totals += $item['quantity'];
            }
        }
        foreach ($itemOdd as $item) {
            if (isset($item['quantity'])) {
                $totals += $item['quantity'];
            }
        }

        $showroom = Showroom::where('id', $requestData['showroom_id'])->first();
        $hubWarehouse = $showroom->hub?->warehouseInHub->first();
        $lastProposalReceipt = ShowroomProposalReceipt::orderByDesc('id')->first();
        $proposalReceiptCode = $lastProposalReceipt ? (int) $lastProposalReceipt->proposal_code + 1 : 1;
        $showroomWarehouse = ShowroomWarehouse::find($requestData['warehouse_receipt_id']);
        $dataCreate = $this->buildProposalData($requestData, $showroomWarehouse->name, $showroomWarehouse->address, $totals);
        $lastProposalHubIssue = ProposalHubIssue::orderByDesc('id')->first();
        $proposalCode = $lastProposalHubIssue ? (int) $lastProposalHubIssue->proposal_code + 1 : 1;
        if ($hubWarehouse) {
            $dataCreateIssue = $this->buildProposalData($requestData, $hubWarehouse->name, $hubWarehouse->hub->address, $totals);
        } else {
            return $response->setError()
                ->setMessage(trans('Trong hub không có kho'));
        }

        DB::beginTransaction();
        try {
            $proposalAgentReceipt = ShowroomProposalReceipt::query()->create(array_merge($dataCreate, [
                'warehouse_receipt_id' => $requestData['warehouse_receipt_id'],
                'proposal_code' => $proposalReceiptCode,
                'warehouse_type' => Warehouse::class,
                'warehouse_id' => $hubWarehouse->id,
            ]));
            $proposalHubIssue = ProposalHubIssue::query()->create(array_merge($dataCreateIssue, [
                'warehouse_id' => $requestData['warehouse_receipt_id'],
                'warehouse_issue_id' => $hubWarehouse->id,
                'warehouse_type' => ShowroomWarehouse::class,
                'proposal_receipt_id' => $proposalAgentReceipt->id,
                'is_batch' => 1,
                'proposal_code' => $proposalCode,
            ]));
            foreach ($items as $value) {
                $product = Product::find($value['product_id']);

                $data = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'sku' => $product->sku,
                    'quantity' => (int) $value['quantity'],
                    'batch_id' => $value['id'],

                ];
                ProposalHubIssueDetail::query()->create(array_merge($data, [
                    'is_batch' => (int) 1,
                    'proposal_id' => $proposalHubIssue->id,
                ]));
                ProposalShowroomReceiptDetail::query()->create(array_merge($data, [
                    'proposal_id' => $proposalAgentReceipt->id,
                ]));
            }
            foreach ($itemOdd as $value) {
                $product = Product::find($value['id']);
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

                ProposalShowroomReceiptDetail::query()->create(array_merge($data, [
                    'proposal_id' => $proposalAgentReceipt->id,
                ]));
            }
            // foreach ($ as $key => $productReceipt) {
            //     $product = Product::find($key);
            //     $data = [
            //         'product_id' => $key,
            //         'product_name' => $product->name,
            //         'sku' => $product->sku,
            //         'price' => $product->price,
            //         'quantity' => $productReceipt['quantity'],
            //     ];
            //     ProposalShowroomReceiptDetail::query()->create(array_merge($data, [
            //         'proposal_id' => $proposalAgentReceipt->id,
            //     ]));
            //     ProposalHubIssueDetail::query()->create(array_merge($data, [
            //         'proposal_id' => $proposalHubIssue->id,
            //         'is_batch' => 1
            //     ]));
            // }
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

        event(new CreatedContentEvent(SHOWROOM_PROPOSAL_RECEIPT_MODULE_SCREEN_NAME, $request, $proposalAgentReceipt));

        return $response
            ->setPreviousUrl(route('proposal-showroom-receipt.index'))
            ->setNextUrl(route('proposal-showroom-receipt.edit', $proposalAgentReceipt->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(ShowroomProposalReceipt $proposalShowroomReceipt, FormBuilder $formBuilder, BaseHttpResponse $response)
    {

        PageTitle::setTitle(trans('Sửa đơn đề xuất :name', ['name' => BaseHelper::clean(get_proposal_receipt_product_code($proposalShowroomReceipt->proposal_code))]));

        abort_if(check_user_depent_of_showroom($proposalShowroomReceipt->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        if ($proposalShowroomReceipt->status == ProposalAgentEnum::PENDING) {
            return $formBuilder->create(ProposalShowroomReceiptForm::class, ['model' => $proposalShowroomReceipt])->renderForm();
        }
        return $response->setError()
            ->setMessage(trans('Không thể truy cập để chỉnh sửa'));
    }

    public function update(ShowroomProposalReceipt $proposalShowroomReceipt, ProposalAgentReceiptRequest $request, BaseHttpResponse $response)
    {
        abort_if(check_user_depent_of_showroom($proposalShowroomReceipt->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');
        if ($proposalShowroomReceipt->status != ProposalAgentEnum::PENDING) {
            return $response
                ->setError()
                ->setMessage('Đơn không thể thay đổi');
        }
        $requestData = $request->input();

        $totals = 0;
        $items = json_decode($requestData['items'], true);
        $itemOdd = json_decode($requestData['itemOdd'], true);
        foreach ($items as $item) {
            if (isset($item['quantity'])) {
                $totals += $item['quantity'];
            }
        }
        foreach ($itemOdd as $item) {
            if (isset($item['quantity'])) {
                $totals += $item['quantity'];
            }
        }

        $showroom = Showroom::where('id', $requestData['showroom_id'])->first();
        $hubWarehouse = $showroom->hub?->warehouseInHub->first();
        $showroomWarehouse = ShowroomWarehouse::find($requestData['warehouse_receipt_id']);
        if ($hubWarehouse) {
            $dataUpdateIssue = $this->buildProposalData($requestData, $hubWarehouse->name, $hubWarehouse->hub->address, $totals);
        } else {
            return $response->setError()
                ->setMessage(trans('Trong hub không có kho hoặc showroom không có hub'));
        }
        $proposalHubIssue = ProposalHubIssue::where([
            'warehouse_type' => ShowroomWarehouse::class,
            'proposal_receipt_id' => $proposalShowroomReceipt->id,
            'status' => 'pending'
        ])->first();
        $dataUpdateReceipt = $this->buildProposalData($requestData, $showroomWarehouse->name, $showroomWarehouse->address, $totals);
        DB::beginTransaction();
        try {
            $proposalShowroomReceipt->update(array_merge($dataUpdateReceipt, [
                'warehouse_receipt_id' => $requestData['warehouse_receipt_id'],
                'warehouse_id' => $hubWarehouse->id,
            ]));

            $proposalHubIssue->update(array_merge($dataUpdateIssue, [
                'warehouse_issue_id' => $hubWarehouse->id,
                'warehouse_id' => $requestData['warehouse_receipt_id'],
            ]));
            foreach ($proposalHubIssue->proposalHubIssueDetail as $proposalHubIssueDetail) {
                $proposalHubIssueDetail->delete();
            }
            $proposalAgentReceiptDetails = ProposalShowroomReceiptDetail::where('proposal_id', $proposalShowroomReceipt->id)->get();
            foreach ($proposalAgentReceiptDetails as $proposalReceiptDetail) {
                $proposalReceiptDetail->delete();
            }

            foreach ($items as $value) {
                $product = Product::find($value['product_id']);

                $data = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'sku' => $product->sku,
                    'quantity' => (int) $value['quantity'],
                    'batch_id' => $value['id'],

                ];
                ProposalHubIssueDetail::query()->create(array_merge($data, [
                    'is_batch' => (int) 1,
                    'proposal_id' => $proposalHubIssue->id,
                ]));
                ProposalShowroomReceiptDetail::query()->create(array_merge($data, [
                    'proposal_id' => $proposalShowroomReceipt->id,
                ]));
            }
            foreach ($itemOdd as $value) {
                $product = Product::find($value['id']);
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

                ProposalShowroomReceiptDetail::query()->create(array_merge($data, [
                    'proposal_id' => $proposalShowroomReceipt->id,
                ]));
            }
            $arrNoti = [
                'action' => 'chỉnh sửa',
                'permission' => "proposal-hub-issue.approve",
                'route' => route('proposal-hub-issue.approveProposalProductIssue', $proposalHubIssue->id),
                'status' => 'chỉnh sửa'
            ];
            send_notify_cms_and_tele($proposalHubIssue, $arrNoti);
            DB::commit();
            event(new UpdatedContentEvent(SHOWROOM_PROPOSAL_RECEIPT_MODULE_SCREEN_NAME, $request, $proposalShowroomReceipt));

            return $response
                ->setPreviousUrl(route('proposal-showroom-receipt.index'))
                ->setMessage(trans('core/base::notices.update_success_message'));
        } catch (Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage(trans($e->getMessage()));
        }
    }

    public function destroy(ShowroomProposalReceipt $proposalShowroomReceipt, Request $request, BaseHttpResponse $response)
    {
        abort_if(check_user_depent_of_showroom($proposalShowroomReceipt->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        DB::beginTransaction();
        try {
            foreach ($proposalShowroomReceipt->proposalIssue->proposalHubIssueDetail as $detailIssue) {
                $detailIssue->delete();
            }
            $proposalShowroomReceipt->proposalIssue->delete();
            foreach ($proposalShowroomReceipt->proposalReceiptDetail as $detailReceipt) {
                $detailReceipt->delete();
            }
            $proposalShowroomReceipt->delete();

            event(new DeletedContentEvent(SHOWROOM_PROPOSAL_RECEIPT_MODULE_SCREEN_NAME, $request, $proposalShowroomReceipt));
            $arrNoti = [
                'action' => 'xóa',
                'route' => '',
                'permission' => 'proposal-showroom-receipt.create',
                'status' => 'xóa'
            ];
            send_notify_cms_and_tele($proposalShowroomReceipt, $arrNoti);
            DB::commit();
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
        $proposal = ShowroomProposalReceipt::where('id', $id)->first();
        $product = ProposalShowroomReceiptDetail::where('proposal_id', $proposal->id)->with([
            'product',
            'product.parentProduct',
            'batch.listProduct',
            'productHubStock' => function ($q) use ($proposal) {
                $q->where('stock_id', $proposal->warehouse->id);
            }
        ])->get();

        $showroom = $proposal->warehouseReceipt->showroom->id;
        return response()->json([
            'data' => $product,
            'proposal' => $proposal,
            'showroom' => $showroom
        ], 200);
    }

    public function approveView(ShowroomProposalReceipt $proposal, BaseHttpResponse $response)
    {
        abort_if(check_user_depent_of_showroom($proposal->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

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
                ->setMessage(__('Đơn hàng đã duyệt hoặc từ chối!'));
        }
        $this->pageTitle(__('Duyệt đơn đề xuất nhập kho đại lý'));
        return view('plugins/showroom::proposal-receipt.approve', compact('proposal'));
    }

    public function approve(Request $request, ShowroomProposalReceipt $proposal, BaseHttpResponse $response)
    {
        abort_if(check_user_depent_of_showroom($proposal->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

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
            'warehouse_type' => ShowroomWarehouse::class,
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
            $proposal->expected_date_submit = Carbon::createFromFormat('d-m-Y', ($requestData['expectDate']));
            $proposal->warehouse_id = $requestData['warehouse_id'];
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
                foreach ($proposalDetails as $proposalDetail) {
                    if ($proposalDetail->product_id == $prd['productId']) {
                        $proposalDetail->quantity_submit = $prd['quantity'];
                        $proposalDetail->save();
                    }
                }
                $productIssueDetail = HubIssueDetail::query()->create($dataProductIssueDetail);
            }

            $arrNoti = [
                'action' => 'tạo',
                'permission' => "hub-issue.confirm",
                'route' => route('hub-issue.view-confirm', $hubIssue->id),
                'status' => 'tạo'
            ];
            send_notify_cms_and_tele($hubIssue, $arrNoti);
            // $arrNoti = [
            //     'action' => 'đã duyệt',
            //     'permission' => "proposal-agent-receipt.create",
            //     'route' => route('hub-issue.view-confirm', $hubIssue->id),
            //     'status' => 'đã duyệt'
            // ];
            // send_notify_cms_and_tele($proposal, $arrNoti);
            DB::commit();
            return $response
                ->setNextUrl(route('proposal-showroom-receipt.index'))
                ->setPreviousUrl(route('proposal-showroom-receipt.index'))
                ->setMessage(trans('Đã duyệt đơn đề xuất'));
        } catch (Exception $e) {
            DB::rollBack();
        }
    }

    private function expectedDate($expected_date)
    {
        return Carbon::createFromFormat('Y-m-d', $expected_date);
    }

    public function view($id)
    {
        $proposal = ShowroomProposalReceipt::where('id', $id)->with('proposalReceiptDetail')->first();
        PageTitle::setTitle('Thông tin nhập kho');
        abort_if(check_user_depent_of_showroom($proposal->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        return view('plugins/showroom::proposal-receipt.view', compact('proposal'));
    }

    public function denied($id, Request $request, BaseHttpResponse $response)
    {
        $proposal = ShowroomProposalReceipt::find($id);
        abort_if(check_user_depent_of_showroom($proposal->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        $proposal->status = ProposalAgentEnum::DENIED;
        $proposal->invoice_confirm_name = Auth::user()->name;
        $proposal->reason_cancel = $request->input('denyReason');
        $proposal->save();
        $arrNoti = [
            'action' => 'từ chối',
            'permission' => "proposal-showroom-receipt.create",
            'route' => route('proposal-showroom-receipt.view', $proposal->id),
            'status' => 'từ chối'
        ];
        send_notify_cms_and_tele($proposal, $arrNoti);
        return $response->setPreviousUrl(route('proposal-showroom-receipt.index'))
            ->setNextUrl(route('proposal-showroom-receipt.index'))->setMessage(trans('Từ chối duyệt đơn'));
    }

    public function getGenerateReceiptProduct(Request $request, ProposalReceiptHelper $issueHelper)
    {
        $data = ShowroomProposalReceipt::with('proposalReceiptDetail')->find($request->id);

        if ($request->button_type === 'print') {
            return $issueHelper->streamInvoice($data);
        }
        return $issueHelper->downloadInvoice($data);
    }
    public function getBatch($id)
    {
        $data = ProductBatch::where(['warehouse_id' => $id, 'warehouse_type' => ShowroomWarehouse::class])->select('product_parent_id')->selectRaw('COUNT(*) as batch_count')
            ->groupBy('product_parent_id')->get();
        return response()->json(['data' => $data, 'success' => 1], 200);
    }
    public function getProductInHub($id, Request $request)
    {
        $showroom = Showroom::find($id);
        $hubWarehouseId = $showroom->hub->warehouseInHub->first()->id;
        $products = ProductBatch::with([
            'product' => function ($query) {
                $query->where('status', 'published');
            },
            'product.productAttribute',
            'listProduct'
        ])
            ->where([
                'warehouse_type' => Warehouse::class,
                'warehouse_id' => $hubWarehouseId,
                'status' => ProductBatchStatusEnum::INSTOCK,

            ])->where('quantity', '>', 0)
            ->get();
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
                ])->limit(50)->get();
        } else {
            $productDetail = QuantityProductInStock::where(['stock_id' => $hubWarehouseId])->where('quantity', '>', 0)->with([
                'product' => function ($query) {
                    $query->where('status', 'published');
                },
                'product.variationInfo',
                'product.parentProduct',
                'product.productAttribute',
            ])->limit(50)->get();

        }

        return response()->json(['data' => $products, 'dataDetail' => $productDetail], 200);

    }
}
