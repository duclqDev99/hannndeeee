<?php

namespace Botble\Agent\Http\Controllers;

use Botble\Agent\Enums\ProposalAgentEnum;
use Botble\Agent\Http\Requests\AgentReceiptRequest;
use Botble\Agent\Models\AgentActualReceipt;
use Botble\Agent\Models\AgentActualReceiptDetail;
use Botble\Agent\Models\AgentProduct;
use Botble\Agent\Models\AgentReceipt;
use Botble\Agent\Models\AgentWarehouse;
use Botble\Agent\Models\ProposalAgentReceipt;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Product;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\WarehouseFinishedProducts\Enums\ApprovedStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProductBatchStatusEnum;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use Botble\Agent\Tables\AgentReceiptTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Agent\Forms\AgentReceiptForm;
use Botble\Agent\Supports\AgentReceiptHelper;
use Botble\Base\Forms\FormBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AgentReceiptController extends BaseController
{
    public function index(AgentReceiptTable $table)
    {
        PageTitle::setTitle(trans('Danh sách'));

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
        PageTitle::setTitle(trans('Xác nhận nhập kho'));
        $productIssue = AgentReceipt::find($id);

        abort_if(check_user_depent_of_agent($productIssue->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        $proposal = ProposalAgentReceipt::find($productIssue->proposal_id);
        if ($productIssue->status->toValue() != ApprovedStatusEnum::PENDING) {
            abort_if($productIssue->status != 'pending', 403, 'Đơn hàng đã nhập kho');
        }
        return view('plugins/agent::receipt.confirm', compact('proposal', 'productIssue'));
    }
    public function confirmQR(AgentReceipt $agentReceipt, Request $request, BaseHttpResponse $response)
    {
        DB::beginTransaction();
        try {
            $agentReceipt->status = ApprovedStatusEnum::APPOROVED;
            $agentReceipt->date_confirm = Carbon::now();
            $agentReceipt->invoice_confirm_name = Auth::user()->name;
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
            $agenActual = AgentActualReceipt::query()->create($dataActual);

            //Tạo 1 mảng chứa id của mỗi sản phẩm riêng biệt và tổng số lượng của chúng để tạo chi tiết cho đơn thực nhập
            $arrProductActual = [];

            foreach ($request->input('batch_ids') as $batchId) {
                $batch = ProductBatch::find($batchId);

                if ($batch) {
                    $batch->update([
                        'status' => ProductBatchStatusEnum::INSTOCK,
                        'warehouse_id' => $agentReceipt->warehouse_receipt_id,
                        'warehouse_type' => AgentWarehouse::class,
                    ]);
                    foreach ($batch->productInBatch as $productBatch) {
                        $agentProduct = AgentProduct::where([
                            'warehouse_id' => $agentReceipt->warehouse_receipt_id,
                            'product_id' => $productBatch->product_id
                        ])->first();
                        if ($agentProduct) {
                            $agentProduct->quantity_qrcode++;
                            $agentProduct->save();
                        } else {
                            AgentProduct::query()->create([
                                'warehouse_id' => $agentReceipt->warehouse_receipt_id,
                                'where_id' => $agentReceipt->warehouse_receipt_id,
                                'where_type' => AgentWarehouse::class,
                                'product_id' => $productBatch->product_id,
                                'quantity_qrcode' => 1
                            ]);
                        }
                        $productBatch->statusQrCode->update([
                            'warehouse_type' => AgentWarehouse::class,
                            'warehouse_id' => $agentReceipt->warehouse_receipt_id,
                            'status' => QRStatusEnum::INSTOCK,
                        ]);
                        // $batch->product->increment('quantity');
                        $product = Product::where('id', $productBatch->product_id)->first();
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
                        $existingRecord = AgentActualReceiptDetail::where([
                            'product_id' => $productBatch->product_id,
                            'batch_id' => $batchId
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
                                'batch_id' => $batch->id,
                                'size' => $size,
                                'color' => $color,

                            ];
                            AgentActualReceiptDetail::create($dataInsertActualDetail);
                        }
                        //Thêm dữ liệu vào mảng chi tiết thực nhập sản phẩm

                    }
                    $qrcode = $batch->getQRCode;

                    if ($qrcode) {
                        $qrcode->update([
                            'status' => QRStatusEnum::INSTOCK,
                            'warehouse_id' => $agentReceipt->warehouse_receipt_id,
                            'warehouse_type' => AgentWarehouse::class,
                        ]);
                    }

                    // $batch->product->increment('quantity');
                }
            }
            //Tạo chi tiết cho đơn thực nhập

            DB::commit();

            return $response
                ->setNextUrl(route('agent-receipt.index'))
                ->setMessage(trans('Đã cập nhật'));
        } catch (Exception $e) {
            DB::rollBack();
            dd($e);
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }
    }
    public function confirm(AgentReceipt $agentReceipt, Request $request, BaseHttpResponse $response)
    {
        abort_if(check_user_depent_of_agent($agentReceipt->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        DB::beginTransaction();
        try {
            $agentReceipt->status = ApprovedStatusEnum::APPOROVED;
            $agentReceipt->date_confirm = Carbon::now();
            $agentReceipt->invoice_confirm_name = Auth::user()->name;
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
            $agenActual = AgentActualReceipt::query()->create($dataActual);

            //Tạo 1 mảng chứa id của mỗi sản phẩm riêng biệt và tổng số lượng của chúng để tạo chi tiết cho đơn thực nhập
            foreach ($agentReceipt->receiptDetail as $receiptDetail) {
                $this->processReceiptDetail($receiptDetail, $agentReceipt, $agenActual);
            }
            $arrNoti = [
                'action' => 'nhập',
                'permission' => "agent-receipt.index",
                'route' => route('agent-receipt.index'),
                'status' => 'nhập'
            ];
            send_notify_cms_and_tele($agentReceipt, $arrNoti);

            DB::commit();

            return $response
                ->setNextUrl(route('agent-receipt.index'))
                ->setMessage(trans('Đã nhập kho'));
        } catch (Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }
    }
    private function processReceiptDetail($receiptDetail, $agentReceipt, $agenActual)
    {
        $batch = ProductBatch::find($receiptDetail->batch_id);
        if ($batch) {
            $this->updateBatchAndProducts($batch, $agentReceipt, $agenActual);
        } else {
            $this->handleNonexistentBatch($receiptDetail, $agentReceipt, $agenActual);
        }
    }
    private function updateBatchAndProducts($batch, $agentReceipt, $agenActual)
    {
        $batch->update([
            'status' => ProductBatchStatusEnum::INSTOCK,
            'warehouse_id' => $agentReceipt->warehouse_receipt_id,
            'warehouse_type' => AgentWarehouse::class,
        ]);

        foreach ($batch->productInBatch as $productBatch) {
            $agentProduct = AgentProduct::where([
                'warehouse_id' => $agentReceipt->warehouse_receipt_id,
                'product_id' => $productBatch->product_id,
            ])->first();

            if ($agentProduct) {
                $agentProduct->where_id = $agentReceipt->warehouse_receipt_id;
                $agentProduct->where_type = AgentWarehouse::class;
                $agentProduct->quantity_qrcode += 1;
                $agentProduct->save();
            } else {
                // Nếu không tìm thấy bản ghi, bạn có thể thực hiện tạo mới bản ghi tại đây.
                $agentProduct = AgentProduct::create([
                    'warehouse_id' => $agentReceipt->warehouse_receipt_id,
                    'product_id' => $productBatch->product_id,
                    'where_id' => $agentReceipt->warehouse_receipt_id,
                    'where_type' => AgentWarehouse::class,
                    'quantity_qrcode' => 1
                ]);
            }
            $productBatch->statusQrCode->update([
                'warehouse_type' => AgentWarehouse::class,
                'warehouse_id' => $agentReceipt->warehouse_receipt_id,
                'status' => QRStatusEnum::INSTOCK,
            ]);

            // $productBatch->product->increment('quantity');

            $product = Product::find($productBatch->product_id);
            $color = '';
            $size = '';

            foreach ($product->variationProductAttributes as $attribute) {
                if ($attribute->color) {
                    $color = $attribute->title;
                }
            }
            foreach ($product->variationProductAttributes as $attribute) {
                if (!$attribute->color) {
                    $size = $attribute->title;
                }
            }
            $dataInsertActualDetail = [
                'actual_id' => $agenActual->id,
                'product_id' => $productBatch->product_id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'quantity' => 1,
                'batch_id' => $batch->id,
                'size' => $size,
                'color' => $color,
                'qrcode_id' =>  $productBatch->statusQrCode->id
            ];
            AgentActualReceiptDetail::create($dataInsertActualDetail);
        }

        $qrcode = $batch->getQRCode;
        if ($qrcode) {
            $qrcode->update([
                'status' => QRStatusEnum::INSTOCK,
                'warehouse_id' => $agentReceipt->warehouse_receipt_id,
                'warehouse_type' => AgentWarehouse::class,
            ]);
        }
    }
    private function handleNonexistentBatch($receiptDetail, $agentReceipt, $agenActual)
    {
        $product = Product::find($receiptDetail->product_id);
        $color = '';
        $size = '';

        foreach ($product->variationProductAttributes as $attribute) {
            if ($attribute->color) {
                $color = $attribute->title;
            }
        }
        foreach ($product->variationProductAttributes as $attribute) {
            if (!$attribute->color) {
                $size = $attribute->title;
            }
        }

        $agentProduct = AgentProduct::where([
            'warehouse_id' => $agentReceipt->warehouse_receipt_id,
            'product_id' => $product->id
        ])->first();

        if ($agentProduct) {
            $agentProduct->where_id = $agentReceipt->warehouse_receipt_id;
            $agentProduct->where_type = AgentWarehouse::class;
            $agentProduct->quantity_qrcode += 1;
            $agentProduct->save();
        } else {
            // Nếu không tìm thấy bản ghi, bạn có thể thực hiện tạo mới bản ghi tại đây.
            $agentProduct = AgentProduct::create([
                'warehouse_id' => $agentReceipt->warehouse_receipt_id,
                'product_id' => $product->id,
                'where_id' => $agentReceipt->warehouse_receipt_id,
                'where_type' => AgentWarehouse::class,
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

        AgentActualReceiptDetail::create($dataInsertActualDetail);

        $qrcode = ProductQrcode::find($receiptDetail->qrcode_id); // Check if qrcode_id exists in $receiptDetail
        if ($qrcode) {
            $qrcode->update([
                'status' => QRStatusEnum::INSTOCK,
                'warehouse_id' => $agentReceipt->warehouse_receipt_id,
                'warehouse_type' => AgentWarehouse::class,
            ]);
        }
    }
    // public function view(AgentReceipt $agentReceipt)
    // {
    //     abort_if(check_user_depent_of_agent($agentReceipt->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

    //     PageTitle::setTitle(trans('Chi tiết phiếu nhập kho'));
    //     // $receipt = $agentReceipt;
    //     // $proposal = $agentReceipt->proposal;
    //     // $actual = $agentReceipt->actualReceipt;
    //     $productIssue =  $agentReceipt;
    //     $actualIssue = AgentActualReceipt::where('receipt_id', $productIssue->id)->first();
    //     return view('plugins/agent::receipt.view', compact('productIssue', 'actualIssue'));
    // }
    public function view(AgentReceipt $agentReceipt)
    {
        PageTitle::setTitle('Thông tin nhập kho');

        abort_if(check_user_depent_of_agent($agentReceipt->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');
        if ($agentReceipt->from_hub_warehouse == 1) {
            $actualIssueHub = $agentReceipt?->proposal?->hubIssue?->actualIssue;
            $batchs = $agentReceipt?->proposal?->hubIssue?->actualQrCode;
        } else {
            $actualIssueHub = $agentReceipt?->proposal?->proposalIssue?->hubIssue?->actualIssue;
            $batchs = $agentReceipt?->proposal?->proposalIssue?->hubIssue?->actualQrCode;
        }
        $actualIssue = $agentReceipt->actualReceipt;
        return view('plugins/agent::receipt.view', compact('agentReceipt', 'actualIssue', 'actualIssueHub', 'batchs'));
    }


    public function getGenerateReceiptProduct(Request $request, AgentReceiptHelper $receiptHelper)
    {
        $data = AgentReceipt::with('receiptDetail')->find($request->id);

        if ($request->button_type === 'print') {
            return $receiptHelper->streamInvoice($data);
        }
        return $receiptHelper->downloadInvoice($data);
    }
    public function denied($id, Request $request, BaseHttpResponse $response)
    {
        DB::beginTransaction();
        $agentReceipt = AgentReceipt::where('id', $id)->sharedLock()->first();

        abort_if(check_user_depent_of_agent($agentReceipt->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        if ($agentReceipt->status == ApprovedStatusEnum::CANCEL) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage('Đơn đã từ chối');
        } else {
            try {

                $agentReceipt->status = ApprovedStatusEnum::CANCEL;
                $agentReceipt->reason_cancel = $request->input('denyReason');
                $agentReceipt->save();
                if ($agentReceipt->from_hub_warehouse == 0) {
                    $agentReceipt->proposal->status = ProposalAgentEnum::REFUSERECEIPT;
                    $agentReceipt->proposal->save();
                }
                ;
                // $arrNoti = [
                //     'action' => 'từ chối',
                //     'permission' => "agent-receipt.index",
                //     'route' => route('agent-receipt.index'),
                //     'status' => 'từ chối'
                // ];
                // send_notify_cms_and_tele($agentReceipt, $arrNoti);
                DB::commit();
                return $response->setPreviousUrl(route('agent-receipt.index'))
                    ->setNextUrl(route('agent-receipt.index'))->setMessage(trans('Từ chối nhập kho'));
            } catch (Exception $e) {
                DB::rollBack();
                return $response
                    ->setError()
                    ->setMessage($e->getMessage());
            }
        }
    }
}
