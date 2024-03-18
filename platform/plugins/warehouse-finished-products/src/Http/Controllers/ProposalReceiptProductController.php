<?php

namespace Botble\WarehouseFinishedProducts\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\WarehouseFinishedProducts\Enums\ProposalReceiptProductEnum;
use Botble\WarehouseFinishedProducts\Models\WarehouseUser;
use Botble\WarehouseFinishedProducts\Http\Requests\ProposalGoodReceiptsRequest;
use Illuminate\Http\Request;
use Exception;
use Botble\WarehouseFinishedProducts\Tables\ProposalReceiptProductTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\WarehouseFinishedProducts\Forms\ProposalGoodReceiptsForm;
use Botble\Base\Forms\FormBuilder;
use Botble\Ecommerce\Models\Product;
use Botble\Warehouse\Forms\WarehouseMaterialForm;
use Botble\Warehouse\Models\ProcessingHouse;
use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;
use Botble\WarehouseFinishedProducts\Http\Requests\ExportBillRequest;
use Botble\WarehouseFinishedProducts\Models\ProductIssue;
use Botble\WarehouseFinishedProducts\Models\ProductIssueDetails;
use Botble\WarehouseFinishedProducts\Models\ProposalProductIssue;
use Botble\WarehouseFinishedProducts\Models\ProposalProductIssueDetails;
use Botble\WarehouseFinishedProducts\Models\ProposalReceiptProductDetails;
use Botble\WarehouseFinishedProducts\Models\ProposalReceiptProducts;
use Botble\WarehouseFinishedProducts\Models\ReceiptProduct;
use Botble\WarehouseFinishedProducts\Models\ReceiptProductDetail;
use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;
use Botble\WarehouseFinishedProducts\Supports\ProposalReceiptHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProposalReceiptProductController extends BaseController
{
    protected string $prefixCodeIssue = 'XK';
    protected string $prefixCodeReceipt = 'NK';

    public function index(ProposalReceiptProductTable $table)
    {
        PageTitle::setTitle('Danh sách đơn đề xuất');
        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        // Assets::addScriptsDirectly(
        //     [
        //         'https://cdn.jsdelivr.net/gh/lelinh014756/fui-toast-js@master/assets/js/toast@1.0.1/fuiToast.min.js',
        //         'vendor/core/plugins/warehouse-finished-products/js/proposal-product.js',
        //     ]
        // )->addStylesDirectly([
        //             'https://cdn.jsdelivr.net/gh/lelinh014756/fui-toast-js@master/assets/css/toast@1.0.1/fuiToast.min.css',
        //             'vendor/core/plugins/warehouse-finished-products/css/form-proposal.css',
        //         ]);
        PageTitle::setTitle('Tạo đơn đề xuất nhập kho thành phẩm');

        return $formBuilder->create(ProposalGoodReceiptsForm::class, ['id' => 'botble-warehouse-forms-proposal-receipt-products-form'])->renderForm();
    }

    public function store(ProposalGoodReceiptsRequest $request, BaseHttpResponse $response)
    {
        $requestData = $request->input();
        $expectedDate = date('Y-m-d', strtotime($requestData['expected_date']));

        $parameter = '';
        $wh_departure_id = null;
        $wh_departure_name = null;
        $is_warehouse = 'warehouse';
        // if ($request->input()['type_proposal'] == 'stock') {
        //     $parameter = 'stock';
        //     $wh_departure_id = $requestData['stock']['detination_wh_id'];
        //     $wh_departure_name = WarehouseFinishedProducts::where(['id' => $wh_departure_id])->first()?->name;
        // } else 
        if ($requestData['is_warehouse'] == 'inventory') {
            $parameter = 'inventory';
            $is_warehouse = 'inventory';
        } else if ($requestData['is_warehouse'] == 'stock-odd') {
            $parameter = 'stock-odd';
            $is_warehouse = 'warehouse-odd';
            $wh_departure_id = $requestData['warehouse_product'];
            $wh_departure_name = WarehouseFinishedProducts::where(['id' => $wh_departure_id])->first()?->name;
        }

        $totalAmount = 0;
        $totalQuantity = 0;
        foreach ($requestData['product'] as $key => $product) {
            $totalAmount += (int) Product::where('id', $key)->first()->price;
            $totalQuantity += ((int) $product['quantity']);
        }

        $warehouseFinished = WarehouseFinishedProducts::where(['id' => $requestData['warehouse_id']])->first();

        // $lastProposalId = ProposalReceiptProducts::orderByDesc('id')->first();
        $lastProposal = ProposalReceiptProducts::orderByDesc('id')->first();
        $proposalCode = $lastProposal ? (int) $lastProposal->proposal_code + 1 : 1;
        //Lấy số thứ tự mã phiếu nhập kho
        // $proposal_code_last = 1;
        // if (!empty($lastProposalId)) {
        //     $proposal_code_last = ((int) $lastProposalId->proposal_code) + 1;
        // }

        $dataPurchase = [
            'general_order_code' => $requestData['general_order_code'] ?? '',
            'proposal_code' => $proposalCode,
            'warehouse_id' => $requestData['warehouse_id'],
            'warehouse_name' => $warehouseFinished->name,
            'warehouse_address' => $warehouseFinished->address,
            'isser_id' => Auth::user()->id,
            'invoice_issuer_name' => Auth::user()->name,
            'wh_departure_id' => $wh_departure_id,
            'wh_departure_name' => $wh_departure_name,
            'is_warehouse' => $is_warehouse,
            'quantity' => $totalQuantity,
            'title' => $requestData['title'],
            'description' => $requestData['description'],
            'expected_date' => $expectedDate,
        ];
        DB::beginTransaction();

        try {
            $productProposal = ProposalReceiptProducts::create($dataPurchase);

            event(new CreatedContentEvent(PROPOSAL_GOOD_RECEIPTS_MODULE_SCREEN_NAME, $request, $productProposal));

            //Tạo thông báo cho admin


            foreach ($requestData['product'] as $key => $product) {
                $processing_house_id = null;
                $processing_house_name = null;

                // if ($parameter == 'processing') {
                //     $processing_house_id = $requestData['processing_id'];
                //     $processing_house_name = ProcessingHouse::where(['id' => $requestData['processing_id']])->first()?->name;
                // }

                $productBy = Product::where(['id' => $key])->first();
                // if (empty($productBy)) {
                //     $productBy = Product::where(['sku' => $product['sku']])->first();
                // }

                $color = '';
                $size = '';

                $arrAttribute = $productBy->variationProductAttributes;

                if (count($arrAttribute) > 0) {
                    if (count($arrAttribute) === 1) {
                        $color = $arrAttribute[0]->color == null ? '' : $arrAttribute[0]->title;
                        $size = $arrAttribute[0]->color == null ? $arrAttribute[0]->title : '';
                    } else if (count($arrAttribute) === 2) {
                        $color = $arrAttribute[0]->color == null ? $arrAttribute[1]->title : $arrAttribute[0]->title;
                        $size = $arrAttribute[0]->color == null ? $arrAttribute[0]->title : $arrAttribute[1]->title;
                    }
                }

                $dataInsert = [
                    'proposal_id' => $productProposal->id,
                    'processing_house_id' => null,
                    'processing_house_name' => null,
                    'sku' => $productBy->sku,
                    'product_name' => $productBy->name,
                    'quantity' => $product['quantity'],
                    'price' => $productBy->price,
                    'product_id' => $key,
                    'color' => $color,
                    'size' => $size,
                ];
                ProposalReceiptProductDetails::create($dataInsert);
                $arrNoti = [
                    'action' => 'tạo',
                    'permission' => "proposal-receipt-products.censorship",
                    'route' => route('proposal-receipt-products.censorship', $productProposal->id),
                    'status' => 'Chờ duyệt'
                ];
                send_notify_cms_and_tele($productProposal, $arrNoti);
                DB::commit();
                return $response
                    ->setPreviousUrl(route('proposal-receipt-products.index'))
                    ->setNextUrl(route('proposal-receipt-products.index'))
                    ->setMessage(trans('core/base::notices.create_success_message'));
            }
        } catch (Exception $err) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage(trans($err->getMessage()));
        }

        //DB commit

    }

    public function edit(ProposalReceiptProducts $proposalReceiptProducts, FormBuilder $formBuilder)
    {
        //nếu đơn đã duyệt hoặc được tạo từ đơn xuất kho khác thì xuất trang 403
        abort_if($proposalReceiptProducts->status == ProposalProductEnum::APPOROVED || !empty($proposalReceiptProducts->proposal_issue_id), 403);
        //Call file js
        PageTitle::setTitle('Chỉnh sửa đơn đề xuất');

        return $formBuilder->create(ProposalGoodReceiptsForm::class, ['model' => $proposalReceiptProducts, 'id' => 'botble-warehouse-forms-proposal-receipt-products-form'])->renderForm();
    }

    public function update(
        ProposalReceiptProducts $proposalReceiptProducts,
        Request $request,
        BaseHttpResponse $response,
    ) {
        if (empty($proposalReceiptProducts)) {
            return $response
                ->setPreviousUrl(route('proposal-receipt-products.index'))
                ->setNextUrl(route('proposal-receipt-products.index'))
                ->setError()
                ->setMessage("Không tìm thấy đơn đề xuất này!!");
        }
        //nếu đơn đã duyệt hoặc được tạo từ đơn xuất kho khác thì xuất trang 403
        abort_if($proposalReceiptProducts->status == ProposalProductEnum::APPOROVED, 403);

        //Check validate data
        $requestData = $request->input();
        $parameter = '';
        $wh_departure_id = null;
        $wh_departure_name = null;
        $wh_departure_address = null;
        $is_warehouse = 'warehouse';

        // if ($request->input()['type_proposal'] == 'stock') {
        //     $parameter = 'stock';

        //     $departureWarehouse = WarehouseFinishedProducts::where(['id' => $requestData['stock']['detination_wh_id']])->first();
        //     $wh_departure_id = $departureWarehouse->id;
        //     $wh_departure_name = $departureWarehouse->name;
        //     $wh_departure_address = $departureWarehouse->address;
        // } else 
        if ($request->is_warehouse == 'inventory') {
            $parameter = 'inventory';
            $is_warehouse = 'inventory';
        } else { //supplier
            $parameter = 'stock-odd';
            $is_warehouse = 'warehouse-odd';
            $departureWarehouse = WarehouseFinishedProducts::where(['id' => $wh_departure_id])->first();
            $wh_departure_id = $departureWarehouse->id;
            $wh_departure_name = $departureWarehouse->name;
            $wh_departure_address = $departureWarehouse->address;
        }

        $totalAmount = 0;
        $totalQuantity = 0;
        foreach ($requestData['product'] as $key => $product) {
            $totalAmount += (int) Product::where(['id' => $key])->first()->price;
            $totalQuantity += ((int) $product['quantity']);
        }
        $warehouseFinished = WarehouseFinishedProducts::where(['id' => $requestData['warehouse_id']])->first();

        $expectedDate = date('Y-m-d', strtotime($requestData['expected_date']));

        $dataProposalUpdate = [
            'general_order_code' => $requestData['general_order_code'] ?? '',
            'warehouse_id' => $requestData['warehouse_id'],
            'warehouse_name' => $warehouseFinished->name,
            'warehouse_address' => $warehouseFinished->address,
            'isser_id' => Auth::user()->id,
            'invoice_issuer_name' => Auth::user()->name,
            'wh_departure_id' => $wh_departure_id,
            'wh_departure_name' => $wh_departure_name,
            'is_warehouse' => $is_warehouse,
            'quantity' => $totalQuantity,
            'title' => $requestData['title'],
            'description' => $requestData['description'],
            'expected_date' => $expectedDate,
            'status' => ProposalProductEnum::PENDING,
            'reasoon_cancel' => null,
        ];
        DB::beginTransaction();

        try {
            $proposalReceiptProducts->update($dataProposalUpdate);
            event(new UpdatedContentEvent(PROPOSAL_GOOD_RECEIPTS_MODULE_SCREEN_NAME, $request, $proposalReceiptProducts));
            //Tạo thông báo cho admin
            $arrNoti = [
                'action' => 'cập nhật',
                'permission' => "proposal-receipt-products.censorship",
                'route' => route('proposal-receipt-products.censorship', $proposalReceiptProducts->id),
                'status' => 'Chờ duyệt'
            ];
            send_notify_cms_and_tele($proposalReceiptProducts, $arrNoti);

            //Delete proposal purchase detail
            foreach ($proposalReceiptProducts->proposalDetail as $key => $proposalDetail) {
                $proposalDetail->delete();
            }

            foreach ($requestData['product'] as $key => $product) {
                $processing_house_id = null;
                $processing_house_name = null;

                $productBy = Product::where(['id' => $key])->first();
                // if (empty($productBy)) {
                //     $productBy = Product::where(['sku' => $product['sku']])->first();
                // }

                $color = '';
                $size = '';

                $arrAttribute = $productBy->variationProductAttributes;
                if (count($arrAttribute) > 0) {
                    if (count($arrAttribute) === 1) {
                        $color = $arrAttribute[0]->color == null ? '' : $arrAttribute[0]->title;
                        $size = $arrAttribute[0]->color == null ? $arrAttribute[0]->title : '';
                    } else if (count($arrAttribute) === 2) {
                        $color = $arrAttribute[0]->color == null ? $arrAttribute[1]->title : $arrAttribute[0]->title;
                        $size = $arrAttribute[0]->color == null ? $arrAttribute[0]->title : $arrAttribute[1]->title;
                    }
                }

                $dataInsertDetail = [
                    'proposal_id' => $proposalReceiptProducts->id,
                    'processing_house_id' => $processing_house_id,
                    'processing_house_name' => $processing_house_name,
                    'sku' => $productBy->sku,
                    'product_name' => $productBy->name,
                    'quantity' => $product['quantity'],
                    'price' => $productBy->price,
                    'product_id' => $productBy->id,
                    'color' => $color,
                    'size' => $size,
                ];

                ProposalReceiptProductDetails::create($dataInsertDetail);
            }
            DB::commit();

            return $response
                ->setPreviousUrl(route('proposal-receipt-products.index'))
                ->setNextUrl(route('proposal-receipt-products.index'))
                ->setMessage(trans('Chỉnh sửa thành công'));
        } catch (Exception $err) {
            DB::rollBack();
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage(trans($err->getMessage()));
        }

        //DB commit

    }

    public function destroy(ProposalReceiptProducts $proposalReceiptProducts, Request $request, BaseHttpResponse $response)
    {
        try {
            abort_if($proposalReceiptProducts->status == ProposalProductEnum::APPOROVED, 403);

            $proposalReceiptProducts->delete();

            event(new DeletedContentEvent(PROPOSAL_GOOD_RECEIPTS_MODULE_SCREEN_NAME, $request, $proposalReceiptProducts));

            //Tạo thông báo cho admin
            $arrNoti = [
                'action' => 'xoá',
                'permission' => "proposal-receipt-products.create",
                'route' => route('proposal-receipt-products.index'),
                'status' => 'Đã xoá đơn'
            ];
            send_notify_cms_and_tele($proposalReceiptProducts, $arrNoti);

            //Get material out by id proposal
            $productProposalIssue = ProposalProductIssue::where(['proposal_receipt_id' => $proposalReceiptProducts->id])->first();
            if (!empty($productProposalIssue)) {
                $productProposalIssue->delete();
                event(new DeletedContentEvent(PROPOSAL_ISSUE_MODULE_SCREEN_NAME, $request, $proposalReceiptProducts));

                foreach ($productProposalIssue->proposalProductIssueDetail as $key => $item) {
                    $item->delete();
                }
            }

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function censorshipProposalReceiptProduct(ProposalReceiptProducts $proposal)
    {
        abort_if($proposal->status == ProposalProductEnum::APPOROVED, 403);
        $proposal = $this->getProposalProductIssue($proposal);
        Assets::addScriptsDirectly([
            'https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js',
            'https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js',
        ])
            ->addScripts(['blockui', 'input-mask']);


        PageTitle::setTitle('Duyệt đơn đề xuất nhập kho thành phẩm');

        return view('plugins/warehouse-finished-products::receipt-product/proposal', compact('proposal'));
    }

    public function approvedProposalReceiptProduct(ProposalReceiptProducts $proposal, Request $request, BaseHttpResponse $response)
    {
        abort_if($proposal->status == ProposalProductEnum::CONFIRM, 403);

        $requestData = $request->input();
        $expectedDate = date('Y-m-d', strtotime($requestData['expected_date']));
        DB::beginTransaction();

        if (empty($proposal)) {
            return $response
                ->setPreviousUrl(route('proposal-receipt-products.index'))
                ->setNextUrl(route('proposal-receipt-products.index'))
                ->setError()
                ->setMessage('Không tìm thấy đơn đề xuất này!!');
        }
        if ($proposal->is_warehouse == 'inventory') {
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

        event(new UpdatedContentEvent(PROPOSAL_GOOD_RECEIPTS_MODULE_SCREEN_NAME, $request, $proposal));

        $totalQuantity = 0;
        foreach ($requestData['product'] as $key => $product) {
            $totalQuantity += ((int) $product['quantity']);
        }

        try {
            if ($proposal->is_warehouse == 'warehouse' || $proposal->is_warehouse == 'warehouse-odd') {
                $issue_code_last = ProductIssue::get()->count() + 1;
                $isBatch = 0;
                if ($proposal->is_warehouse == 'warehouse') {
                    $isBatch = 1;
                }

                //Tạo phiếu xuất kho
                $dataProductIssue = [
                    'proposal_id' => $proposal->id,
                    'warehouse_id' => $proposal->wh_departure_id,
                    'warehouse_issue_type' => WarehouseFinishedProducts::class,
                    'warehouse_name' => $proposal->wh_departure_name,
                    'warehouse_address' => '',
                    'warehouse_type' => WarehouseFinishedProducts::class,
                    'issuer_id' => Auth::user()->id,
                    'invoice_issuer_name' => Auth::user()->name,
                    'warehouse_receipt_id' => $proposal->warehouse_id,
                    'quantity' => $totalQuantity,
                    'title' => $proposal->title,
                    'description' => $requestData['description'],
                    'expected_date' => $expectedDate,
                    'general_order_code' => $proposal->general_order_code,
                    'issue_code' => $issue_code_last,
                    'status' => 'pending',
                    'is_warehouse' => 1,
                    'from_proposal_receipt' => 1,
                    'is_batch' => $isBatch
                ];

                $productIssue = ProductIssue::query()->create($dataProductIssue);
                event(new CreatedContentEvent(PROPOSAL_ISSUE_MODULE_SCREEN_NAME, $request, $productIssue));

                // //Notify for stock
                $arrNoti = [
                    'action' => 'tạo',
                    'permission' => "proposal-product-issue.approve",
                    'route' => route('proposal-product-issue.approveProposalProductIssue', $productIssue),
                    'status' => 'Chờ xuất kho'
                ];
                send_notify_cms_and_tele($productIssue, $arrNoti);
            } else {
                //Tạo phiếu nhập kho
                $receipt_code = ReceiptProduct::get()->count() + 1;
                //Data insert
                $dataReceipt = [
                    'proposal_id' => $proposal->id,
                    'warehouse_id' => $proposal->warehouse_id,
                    'warehouse_name' => $proposal->warehouse_name,
                    'warehouse_address' => $proposal->warehouse_address,
                    'warehouse_type' => WarehouseFinishedProducts::class,
                    'isser_id' => Auth::user()->id,
                    'invoice_issuer_name' => Auth::user()->name,
                    'warehouse_receipt_id' => $proposal->warehouse_id,
                    'wh_departure_id' => $proposal->wh_departure_id,
                    'wh_departure_name' => $proposal->wh_departure_name,
                    'quantity' => $totalQuantity,
                    'title' => $proposal->title,
                    'description' => $requestData['description'],
                    'expected_date' => $expectedDate,
                    'general_order_code' => $proposal->general_order_code,
                    'receipt_code' => $receipt_code,
                    'status' => 'pending',
                    'is_warehouse' => 'inventory',
                    'from_product_issue' => false,
                ];
                $receiptProduct = ReceiptProduct::query()->create($dataReceipt);
                event(new CreatedContentEvent(RECEIPT_PRODUCT_MODULE_SCREEN_NAME, $request, $receiptProduct));

                $proposal->update([
                    'proposal_code' => $receipt_code,
                ]);

                // //Notify for stock
                $arrNoti = [
                    'action' => 'tạo',
                    'permission' => "receipt-product.censorship",
                    'route' => route('receipt-product.censorship', $receiptProduct),
                    'status' => 'Chờ nhập kho'
                ];
                send_notify_cms_and_tele($receiptProduct, $arrNoti);
            }
            // //Notify for creator proposal
            $arrNotiCreator = [
                'action' => 'duyệt',
                'permission' => "proposal-receipt-products.create",
                'route' => route('proposal-receipt-products.view', $proposal->id),
                'status' => 'Đã duyệt'
            ];
            send_notify_cms_and_tele($proposal, $arrNotiCreator);
        } catch (Exception $err) {
            DB::rollBack();
            throw new Exception($err->getMessage(), 1);
        }
        try {
            for ($i = 0; $i < count($requestData['product']); $i++) {

                if ($proposal->is_warehouse == 'warehouse') {

                    $dataProductIssueDetail = [
                        'product_issue_id' => $productIssue->id,
                        'product_id' => $proposal->proposalDetail[$i]->product_id,
                        'product_name' => $proposal->proposalDetail[$i]->product_name,
                        'color' => $proposal->proposalDetail[$i]->color,
                        'size' => $proposal->proposalDetail[$i]->size,
                        'sku' => $proposal->proposalDetail[$i]->sku,
                        'price' => $requestData['product'][$proposal->proposalDetail[$i]->id]['product_price'],
                        'quantity' => $requestData['product'][$proposal->proposalDetail[$i]->id]['quantity'],
                        'is_batch' => 1
                    ];
                    ProductIssueDetails::create($dataProductIssueDetail);
                } else if ($proposal->is_warehouse == 'warehouse-odd') {
                    $dataProductIssueDetail = [
                        'product_issue_id' => $productIssue->id,
                        'product_id' => $proposal->proposalDetail[$i]->product_id,
                        'product_name' => $proposal->proposalDetail[$i]->product_name,
                        'color' => $proposal->proposalDetail[$i]->color,
                        'size' => $proposal->proposalDetail[$i]->size,
                        'sku' => $proposal->proposalDetail[$i]->sku,
                        'price' => $requestData['product'][$proposal->proposalDetail[$i]->id]['product_price'],
                        'quantity' => $requestData['product'][$proposal->proposalDetail[$i]->id]['quantity'],
                        'is_batch' => 0
                    ];
                    ProductIssueDetails::create($dataProductIssueDetail);
                } else {
                    $dataReceiptDetail = [
                        'receipt_id' => $receiptProduct->id,
                        'product_id' => $proposal->proposalDetail[$i]->product_id,
                        'product_name' => $proposal->proposalDetail[$i]->product_name,
                        'price' => $requestData['product'][$proposal->proposalDetail[$i]->id]['product_price'],
                        'sku' => $proposal->proposalDetail[$i]->sku,
                        'quantity' => $requestData['product'][$proposal->proposalDetail[$i]->id]['quantity'],
                        'color' => $proposal->proposalDetail[$i]->color,
                        'size' => $proposal->proposalDetail[$i]->size,
                    ];
                    ReceiptProductDetail::create($dataReceiptDetail);
                }
            }
        } catch (Exception $err) {
            DB::rollBack();
            throw new Exception($err->getMessage(), 1);
        }

        //DB commit
        DB::commit();

        return $response
            ->setPreviousUrl(route('proposal-receipt-products.index'))
            ->setNextUrl(route('proposal-receipt-products.index'))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function cancelProposalReceiptProduct(ProposalReceiptProducts $proposal, Request $request, BaseHttpResponse $response)
    {
        if (empty($proposal)) {
            return $response->setError()->setMessage('Không tìm thấy đơn đề xuất này!!');
        }
        if ($proposal->status == ProposalProductEnum::APPOROVED) {
            return $response->setError()->setMessage('Đơn đề xuất này đã được duyệt, không thể huỷ đơn!!');
        }

        $proposal->update([
            'reasoon_cancel' => $request->input()['reasoon'],
            'status' => ProposalProductEnum::DENIED,
            'invoice_confirm_name' => Auth::user()->name,
            'date_confirm' => Carbon::now()->format('Y-m-d')
        ]);
        event(new UpdatedContentEvent(PROPOSAL_GOOD_RECEIPTS_MODULE_SCREEN_NAME, $request, $proposal));

        //Thông báo hủy đơn cho người tạo đơn
        $arrNotiCreator = [
            'action' => 'hủy',
            'permission' => "proposal-receipt-products.create",
            'route' => route('proposal-receipt-products.view', $proposal->id),
            'status' => 'Đã huỷ đơn'
        ];
        send_notify_cms_and_tele($proposal, $arrNotiCreator);

        return $response
            ->setPreviousUrl(route('proposal-receipt-products.index'))
            ->setNextUrl(route('proposal-receipt-products.index'))
            ->setMessage("Huỷ đơn đề xuất thành công!!");
    }

    public function viewProposalReceiptProduct(ProposalReceiptProducts $proposal)
    {
        PageTitle::setTitle('Thông tin nhập kho');

        $receipt = ReceiptProduct::where('proposal_id', $proposal->id)->first();
        $proposal = $this->getProposalProductIssue($proposal);
        return view('plugins/warehouse-finished-products::receipt-product.view', compact('proposal', 'receipt'));
    }
    private function getProposalProductIssue($proposal)
    {
        if (!request()->user()->hasPermission('warehouse-finished-products.warehouse-all')) {
            $warehouseUsers = WarehouseUser::where('user_id', \Auth::id())->get();
            $warehouseIds = $warehouseUsers->pluck('warehouse_id')->toArray();
            if (!in_array($proposal->warehouse_id, $warehouseIds)) {
                abort(403, 'Không có quyền xem');
            }
        }
        return $proposal;
    }

    public function getGenerateReceiptProduct(Request $request, ProposalReceiptHelper $proposalHelper)
    {
        $data = ProposalReceiptProducts::with('proposalDetail')->find($request->id);

        if ($request->button_type === 'print') {
            return $proposalHelper->streamInvoice($data);
        }
        return $proposalHelper->downloadInvoice($data);
    }
    public function getProposal($id)
    {
        $data = ProposalReceiptProducts::where('id', $id)->with('proposalDetail.prd.parentProduct')->first();
        return response()->json(['data' => $data, 'err' => 0], 200);
    }
}
