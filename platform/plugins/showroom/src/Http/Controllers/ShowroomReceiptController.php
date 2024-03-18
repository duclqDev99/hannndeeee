<?php

namespace Botble\Showroom\Http\Controllers;

use Botble\Agent\Enums\ProposalAgentEnum;
use Botble\Agent\Forms\AgentReceiptForm;
use Botble\Agent\Http\Requests\AgentReceiptRequest;
use Botble\Agent\Models\AgentReceipt;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Models\Product;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\Showroom\Models\ShowroomActualReceipt;
use Botble\Showroom\Models\ShowroomActualReceiptDetail;
use Botble\Showroom\Models\ShowroomProduct;
use Botble\Showroom\Models\ShowroomProposalReceipt;
use Botble\Showroom\Models\ShowRoomReceipt;
use Botble\Showroom\Models\ShowroomWarehouse;
use Botble\Showroom\Supports\ShowroomReceiptHelper;
use Botble\Showroom\Tables\ShowroomReceiptTable;
use Botble\WarehouseFinishedProducts\Enums\ApprovedStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProductBatchStatusEnum;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShowroomReceiptController extends BaseController
{
    public function index(ShowroomReceiptTable $table)
    {
        PageTitle::setTitle(trans('Danh sách phiếu nhập kho'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/agent::agent-receipt.create'));

        return $formBuilder->create(AgentReceiptForm::class)->renderForm();
    }

    public function store(AgentReceiptRequest $request, BaseHttpResponse $response)
    {
        $agentReceipt = AgentReceipt::query()->create($request->input());

        event(new CreatedContentEvent(AGENT_RECEIPT_MODULE_SCREEN_NAME, $request, $agentReceipt));

        return $response
            ->setPreviousUrl(route('agent-receipt.index'))
            ->setNextUrl(route('agent-receipt.edit', $agentReceipt->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(AgentReceipt $agentReceipt, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $agentReceipt->name]));

        return $formBuilder->create(AgentReceiptForm::class, ['model' => $agentReceipt])->renderForm();
    }

    public function update(AgentReceipt $agentReceipt, AgentReceiptRequest $request, BaseHttpResponse $response)
    {
        $agentReceipt->fill($request->input());

        $agentReceipt->save();

        event(new UpdatedContentEvent(AGENT_RECEIPT_MODULE_SCREEN_NAME, $request, $agentReceipt));

        return $response
            ->setPreviousUrl(route('agent-receipt.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(AgentReceipt $agentReceipt, Request $request, BaseHttpResponse $response)
    {
        try {

            $agentReceipt->delete();

            event(new DeletedContentEvent(AGENT_RECEIPT_MODULE_SCREEN_NAME, $request, $agentReceipt));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function confirmView(int|string $id, BaseHttpResponse $response)
    {
        $productIssue = ShowRoomReceipt::find($id);

        abort_if(check_user_depent_of_showroom($productIssue->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        $proposal = ShowroomProposalReceipt::find($productIssue->proposal_id);
        if ($productIssue->status->toValue() !== ApprovedStatusEnum::PENDING) {
            return $response
                ->setError()
                ->setMessage(__('Đơn hàng đã nhập kho'));
        }
        return view('plugins/showroom::receipt.confirm', compact('proposal', 'productIssue'));
    }
    public function confirmQR(ShowRoomReceipt $agentReceipt, Request $request, BaseHttpResponse $response)
    {
        DB::beginTransaction();
        try {
            $agentReceipt->status = ApprovedStatusEnum::APPOROVED;
            $agentReceipt->invoice_confirm_name = Auth::user()->name;
            $agentReceipt->date_confirm = Carbon::now();
            $agentReceipt->save();
            if ($agentReceipt->from_hub_warehouse == 0) {
                $agentReceipt->proposal->status = ProposalAgentEnum::CONFIRM;
                $agentReceipt->proposal->save();
            }
            $filteredImages = array_filter($request->input('images'));
            $imageJson = json_encode($filteredImages);
            $dataActual = [
                'receipt_id' => $agentReceipt->id,
                'image' => $imageJson
            ];
            $agenActual = ShowroomActualReceipt::query()->create($dataActual);
            $arrProductActual = [];
            foreach ($request->input('batch_ids') as $batchId) {
                $batch = ProductBatch::find($batchId);
                if ($batch) {
                    $batch->update([
                        'status' => ProductBatchStatusEnum::INSTOCK,
                        'warehouse_id' => $agentReceipt->warehouse_receipt_id,
                        'warehouse_type' => ShowroomWarehouse::class,
                    ]);
                    foreach ($batch->productInBatch as $productBatch) {
                        $agentProduct = ShowroomProduct::where([
                            'warehouse_id' => $agentReceipt->warehouse_receipt_id,
                            'product_id' => $productBatch->product_id
                        ])->first();
                        if ($agentProduct) {
                            $agentProduct->quantity_qrcode++;
                            $agentProduct->save();
                        } else {
                            ShowroomProduct::query()->create([
                                'warehouse_id' => $agentReceipt->warehouse_receipt_id,
                                'where_id' => $agentReceipt->warehouse_receipt_id,
                                'where_type' => ShowroomWarehouse::class,
                                'product_id' => $productBatch->product_id,
                                'quantity_qrcode' => 1
                            ]);
                        }
                        $productBatch->statusQrCode->update([
                            'warehouse_type' => ShowroomWarehouse::class,
                            'warehouse_id' => $agentReceipt->warehouse_receipt_id,
                            'status' => QRStatusEnum::INSTOCK,
                        ]);
                        // $productBatch->product->increment('quantity');
                        $product = Product::where('id', $productBatch->product_id)->first();
                        $existingRecord = ShowroomActualReceiptDetail::where([
                            'product_id' => $productBatch->product_id,
                            'batch_id' => $batch->id
                        ])->first();
                        if ($existingRecord) {
                            $existingRecord->increment('quantity');
                        } else {
                            $dataInsertActualDetail = [
                                'actual_id' => $agenActual->id,
                                'product_id' => $productBatch->product_id,
                                'product_name' => $product->name,
                                'sku' => $product->sku,
                                'price' => $product->price,
                                'quantity' => 1,
                                'batch_id' => $batch->id
                            ];
                            ShowroomActualReceiptDetail::create($dataInsertActualDetail);
                        }
                    }
                    $qrcode = $batch->getQRCode;

                    if ($qrcode) {
                        $qrcode->update([
                            'status' => QRStatusEnum::INSTOCK,
                            'warehouse_id' => $agentReceipt->warehouse_receipt_id,
                            'warehouse_type' => ShowroomWarehouse::class,
                        ]);
                    }

                    // $batch->product->increment('quantity');

                }

            }

            DB::commit();

            return $response
                ->setNextUrl(route('showroom-receipt.index'))
                ->setMessage(trans('Đã cập nhật'));
        } catch (Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }
    }
    public function confirm(ShowRoomReceipt $agentReceipt, Request $request, BaseHttpResponse $response)
    {
        $agentReceipt = ShowRoomReceipt::where('id', $agentReceipt->id)->sharedLock()->first();

        abort_if(check_user_depent_of_showroom($agentReceipt->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        if ($agentReceipt->status == ApprovedStatusEnum::PENDING) {
            DB::beginTransaction();
            try {
                $agentReceipt->status = ApprovedStatusEnum::APPOROVED;
                $agentReceipt->invoice_confirm_name = Auth::user()->name;
                $agentReceipt->date_confirm = Carbon::now();
                $agentReceipt->save();
                if ($agentReceipt->from_hub_warehouse == 0) {
                    $agentReceipt->proposal->status = ProposalAgentEnum::CONFIRM;
                    $agentReceipt->proposal->save();
                }
                $filteredImages = array_filter($request->input('images'));
                $imageJson = json_encode($filteredImages);
                $dataActual = [
                    'receipt_id' => $agentReceipt->id,
                    'image' => $imageJson
                ];
                $agenActual = ShowroomActualReceipt::query()->create($dataActual);
                foreach ($agentReceipt->receiptDetail as $receiptDetail) {
                    $batch = ProductBatch::find($receiptDetail->batch_id);
                    if ($batch) {
                        $batch->update([
                            'status' => ProductBatchStatusEnum::INSTOCK,
                            'warehouse_id' => $agentReceipt->warehouse_receipt_id,
                            'warehouse_type' => ShowroomWarehouse::class,
                        ]);
                        foreach ($batch->productInBatch as $productBatch) {
                            $showroomProduct = ShowroomProduct::where(
                                [
                                    'warehouse_id' => $agentReceipt->warehouse_receipt_id,
                                    'product_id' => $productBatch->product_id
                                ]
                            )->first();
                            if ($showroomProduct) {
                                $showroomProduct->increment('quantity_qrcode');
                            } else {
                                $showroomProduct = ShowroomProduct::create([
                                    'warehouse_id' => $agentReceipt->warehouse_receipt_id,
                                    'product_id' => $productBatch->product_id,
                                    'where_id' => $agentReceipt->warehouse_receipt_id,
                                    'where_type' => ShowroomWarehouse::class,
                                    'quantity_qrcode' => 1
                                ]);
                            }
                            $productBatch->statusQrCode->update([
                                'warehouse_type' => ShowroomWarehouse::class,
                                'warehouse_id' => $agentReceipt->warehouse_receipt_id,
                                'status' => QRStatusEnum::INSTOCK,
                            ]);
                            $product = Product::where('id', $productBatch->product_id)->first();
                            $dataInsertActualDetail = [
                                'actual_id' => $agenActual->id,
                                'product_id' => $productBatch->product_id,
                                'product_name' => $product->name,
                                'sku' => $product->sku,
                                'price' => $product->price,
                                'quantity' => 1,
                                'batch_id' => $batch->id,
                                'qrcode_id' =>  $productBatch->statusQrCode->id
                            ];
                            ShowroomActualReceiptDetail::create($dataInsertActualDetail);
                        }
                        $qrcode = $batch->getQRCode;
                        if ($qrcode) {
                            $qrcode->update([
                                'status' => QRStatusEnum::INSTOCK,
                                'warehouse_id' => $agentReceipt->warehouse_receipt_id,
                                'warehouse_type' => ShowroomWarehouse::class,
                            ]);
                        }
                    } else {
                        $this->handleNonexistentBatch($receiptDetail, $agentReceipt, $agenActual);
                    }
                }
                $arrNoti = [
                    'action' => 'xác nhận nhập',
                    'permission' => "showroom-receipt.index",
                    'route' => route('showroom-receipt.index'),
                    'status' => 'xác nhận nhập'
                ];
                send_notify_cms_and_tele($agentReceipt, $arrNoti);
                DB::commit();
                return $response
                    ->setNextUrl(route('showroom-receipt.index'))
                    ->setMessage(trans('Đã cập nhật'));
            } catch (Exception $e) {
                DB::rollBack();
                return $response
                    ->setError()
                    ->setMessage($e->getMessage());
            }

        } else {
            return $response
                ->setError()
                ->setMessage('Đơn đã được xác nhận');
        }
    }

    public function denied($id, Request $request, BaseHttpResponse $response)
    {
        DB::beginTransaction();
        $showroomReceipt = ShowRoomReceipt::where('id', $id)->sharedLock()->first();

        abort_if(check_user_depent_of_showroom($showroomReceipt->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        if ($showroomReceipt->status == ApprovedStatusEnum::CANCEL) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage('Đơn đã từ chối');
        } else {
            try {

                $showroomReceipt->status = ApprovedStatusEnum::CANCEL;
                $showroomReceipt->reason_cancel = $request->input('denyReason');
                $showroomReceipt->save();
                if ($showroomReceipt->from_hub_warehouse == 0) {
                    $showroomReceipt->proposal->status = ProposalAgentEnum::REFUSERECEIPT;
                    $showroomReceipt->proposal->save();
                }
                ;
                $arrNoti = [
                    'action' => 'từ chối',
                    'permission' => "showroom-receipt.index",
                    'route' => route('showroom-receipt.index'),
                    'status' => 'từ chối'
                ];
                send_notify_cms_and_tele($showroomReceipt, $arrNoti);
                DB::commit();
                return $response->setPreviousUrl(route('showroom-receipt.index'))
                    ->setNextUrl(route('showroom-receipt.index'))->setMessage(trans('Từ chối duyệt đơn'));
            } catch (Exception $e) {
                DB::rollBack();
                return $response
                    ->setError()
                    ->setMessage($e->getMessage());
            }
        }
    }
    private function handleNonexistentBatch($receiptDetail, $showroomReceipt, $agenActual)
    {

        $product = Product::find($receiptDetail->product_id);
        $color = '';
        $size = '';

        foreach ($product->variationProductAttributes as $attribute) {
            if ($attribute->color) {
                $color = $attribute->title;
            } else {
                $size = $attribute->title;
            }
        }

        $showroomProduct = ShowroomProduct::where(
            [
                'warehouse_id' => $showroomReceipt->warehouse_receipt_id,
                'product_id' => $receiptDetail->product_id
            ]
        )->first();
        if ($showroomProduct) {
            $showroomProduct->increment('quantity_qrcode');
        } else {
            $showroomProduct = ShowroomProduct::create([
                'warehouse_id' => $showroomReceipt->warehouse_receipt_id,
                'product_id' => $receiptDetail->product_id,
                'where_id' => $showroomReceipt->warehouse_receipt_id,
                'where_type' => ShowroomWarehouse::class,
                'quantity_qrcode' => 1
            ]);
        }
        $dataInsertActualDetail = [
            'actual_id' => $agenActual->id,
            'product_id' => $receiptDetail->product_id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'price' => $product->price,
            'quantity' => $receiptDetail->quantity,
            'size' => $size,
            'color' => $color,
            'qrcode_id' => $receiptDetail->qrcode_id // Check if qrcode_id exists in $receiptDetail
        ];

        ShowroomActualReceiptDetail::create($dataInsertActualDetail);

        $qrcode = ProductQrcode::find($receiptDetail->qrcode_id); // Check if qrcode_id exists in $receiptDetail
        if ($qrcode) {
            $qrcode->update([
                'status' => QRStatusEnum::INSTOCK,
                'warehouse_id' => $showroomReceipt->warehouse_receipt_id,
                'warehouse_type' => ShowroomWarehouse::class,
            ]);
        }
    }
    public function view($id)
    {
        PageTitle::setTitle('Thông tin nhập kho');
        $productIssue = ShowRoomReceipt::find($id);

        abort_if(check_user_depent_of_showroom($productIssue->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');
        if ($productIssue->from_hub_warehouse == 1) {
            $actualIssueHub = $productIssue?->proposal?->hubIssue?->actualIssue;
            $batchs = $productIssue?->proposal?->hubIssue?->actualQrCode;
        } else {
            $actualIssueHub = $productIssue?->proposal?->proposalIssue?->hubIssue?->actualIssue;
            $batchs = $productIssue?->proposal?->proposalIssue?->hubIssue?->actualQrCode;
        }
        $actualIssue = ShowroomActualReceipt::where('receipt_id', $id)->first();
        return view('plugins/showroom::receipt.view', compact('productIssue', 'actualIssue', 'actualIssueHub', 'batchs'));
    }

    public function getGenerateReceiptProduct(Request $request, ShowroomReceiptHelper $issueHelper)
    {
        $data = ShowRoomReceipt::with('receiptDetail')->find($request->id);

        if ($request->button_type === 'print') {
            return $issueHelper->streamInvoice($data);
        }
        return $issueHelper->downloadInvoice($data);
    }
}
