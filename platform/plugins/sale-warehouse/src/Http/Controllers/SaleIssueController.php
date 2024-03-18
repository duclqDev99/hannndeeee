<?php

namespace Botble\SaleWarehouse\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Models\Product;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\SaleWarehouse\Http\Requests\SaleIssueRequest;
use Botble\SaleWarehouse\Models\ActualIssueQrCode;
use Botble\SaleWarehouse\Models\SaleActualIssue;
use Botble\SaleWarehouse\Models\SaleActualIssueDetail;
use Botble\SaleWarehouse\Models\SaleIssue;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\SaleWarehouse\Models\SaleProduct;
use Botble\SaleWarehouse\Models\SaleWarehouseChild;
use Botble\WarehouseFinishedProducts\Enums\ProductBatchStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProductIssueStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProposalIssueStatusEnum;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Botble\WarehouseFinishedProducts\Models\ProductBatchDetail;
use Botble\WarehouseFinishedProducts\Models\ProductQrHistotry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use Botble\SaleWarehouse\Tables\SaleIssueTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\SaleWarehouse\Forms\SaleIssueForm;
use Botble\Base\Forms\FormBuilder;
use Botble\HubWarehouse\Models\IssueInputTour;
use Botble\SaleWarehouse\Models\SaleWarehouse;
use Botble\SaleWarehouse\Repositories\Interfaces\SaleIssueRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Str;

class SaleIssueController extends BaseController
{
    protected $saleIssueRepository;

    public function __construct(SaleIssueRepositoryInterface $saleIssueRepository)
    {
        $this->saleIssueRepository = $saleIssueRepository;
    }
    public function index(SaleIssueTable $table)
    {
        PageTitle::setTitle(trans('plugins/sale-warehouse::sale-issue.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/sale-warehouse::sale-issue.create'));

        return $formBuilder->create(SaleIssueForm::class)->renderForm();
    }

    public function store(SaleIssueRequest $request, BaseHttpResponse $response)
    {
        $saleIssue = SaleIssue::query()->create($request->input());

        event(new CreatedContentEvent(SALE_ISSUE_MODULE_SCREEN_NAME, $request, $saleIssue));

        return $response
            ->setPreviousUrl(route('sale-issue.index'))
            ->setNextUrl(route('sale-issue.edit', $saleIssue->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(SaleIssue $saleIssue, FormBuilder $formBuilder, BaseHttpResponse $response)
    {
        if ($saleIssue->whereIn('warehouse_issue_id', get_list_sale_warehouse_id_for_current_user())->exists() || Auth::user()->hasPermission('sale-warehouse.all')) {
            PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $saleIssue->name]));
            return $formBuilder->create(SaleIssueForm::class, ['model' => $saleIssue])->renderForm();
        }
        return $response
            ->setError()
            ->setMessage(__('Không có quyền truy cập'));
    }

    public function update(SaleIssue $saleIssue, SaleIssueRequest $request, BaseHttpResponse $response)
    {
        $saleIssue->fill($request->input());

        $saleIssue->save();

        event(new UpdatedContentEvent(SALE_ISSUE_MODULE_SCREEN_NAME, $request, $saleIssue));

        return $response
            ->setPreviousUrl(route('sale-issue.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(SaleIssue $saleIssue, Request $request, BaseHttpResponse $response)
    {
        try {
            $saleIssue->delete();

            event(new DeletedContentEvent(SALE_ISSUE_MODULE_SCREEN_NAME, $request, $saleIssue));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
    public function viewConfirm($id, BaseHttpResponse $response)
    {
        Assets::addScriptsDirectly([
            'vendor/core/plugins/gallery/js/gallery-admin.js',
            'https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js',
            'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js'
        ]);
        PageTitle::setTitle('Phiếu xuất kho');
        $productIssue = SaleIssue::where('id', $id)->with('productIssueDetail')->first();
        if ($productIssue->whereIn('warehouse_issue_id', get_list_sale_warehouse_id_for_current_user())->exists() || Auth::user()->hasPermission('sale-warehouse.all')) {
            if (!$productIssue) {
                return $response
                    ->setError()
                    ->setMessage(__('Không có quyền truy cập'));
            }
            if ($productIssue->status->toValue() != ProductIssueStatusEnum::PENDING && $productIssue->status->toValue() != ProductIssueStatusEnum::PENDINGISSUE) {
                return $response
                    ->setError()
                    ->setMessage(__('Không có quyền truy cập'));
            }

            return view('plugins/sale-warehouse::sale-issue.confirm', compact('productIssue'));
        } else {
            return $response
                ->setError()
                ->setMessage(__('Không có quyền truy cập'));
        }
    }
    public function createBatchIssue(Request $request, $type)
    {
        $requestData = $request->input();

        DB::beginTransaction();

        try {
            $products = $requestData['products'] ?? [];
            $batchs = $requestData['batchs'] ?? [];

            $total = array_reduce($batchs, function ($carry, $batch) {
                return $carry + $batch['reference']['quantity'];
            }, 0);
            $total += count($products);
            if ($total == 0) {
                return response()->json(['err' => 1, 'msg' => 'Không có số lượng được quét'], 200);
            }
            $saleIssue = SaleIssue::where('id', $requestData['id'])->first();
            $saleIssue->update([
                'status' => ProductIssueStatusEnum::PENDINGISSUE
            ]);
            $saleIssueDetail = $saleIssue->productIssueDetail;
            if ($type == 'batch') {
                $prefix = "BAT-SAL";
                $lastBatch = ProductBatch::orderByDesc('id')->first();
                if (empty($lastBatch)) {
                    $lastProductBatch = 1;
                    $batch_code = str_pad($lastProductBatch, 7, '0', STR_PAD_LEFT);
                } else {
                    $productBatch = (int) substr($lastBatch->batch_code, 7);
                    $batch_code = str_pad($productBatch + 1, 7, '0', STR_PAD_LEFT);
                }

                $batch_code_last = $prefix . $batch_code;
                $dataCreateBatch = [
                    'batch_code' => $batch_code_last,
                    'quantity' => $total,
                    'start_qty' => $total,
                    'status' => ProductBatchStatusEnum::OUTSTOCK,
                    'warehouse_id' => $saleIssue->warehouse_issue_id,
                    'warehouse_type' => SaleWarehouseChild::class,
                    'product_parent_id' => 0,
                ];
                $productBatch = ProductBatch::query()->create($dataCreateBatch);

                $randomString = Str::random(7);
                $dateTimeNow = Carbon::now()->format('ymdHis');
                $qrCode = $randomString . $dateTimeNow;
                $qrCodeWithLogo = QrCode::size(150)->format('png')->merge('images/logo-handee.png', 0.3, true)->errorCorrection('H')->generate($qrCode);
                $proBatch = ProductQrcode::query()->create([
                    'qr_code' => $qrCode,
                    'base_code_64' => base64_encode($qrCodeWithLogo),
                    'batch_id' => $productBatch->id,
                    'status' => QRStatusEnum::PENDING,
                    'warehouse_id' => $saleIssue->warehouse_issue_id,
                    'warehouse_type' => SaleWarehouseChild::class,
                    'reference_id' => $productBatch->id,
                    'created_by' => Auth::user()->id,
                    'reference_type' => ProductBatch::class,
                ]);
                ActualIssueQrCode::query()->create([
                    'issue_id' => $saleIssue->id,
                    'qrcode_id' => $proBatch->id,
                    'batch_id' => $productBatch->id,
                    'is_batch' => 1,
                ]);
                //Tạo thông tin chi tiết cho từng lô
                foreach ($batchs as $batch) {
                    $batchQrcode = ProductQrcode::find($batch['id']);
                    $batchQrcode->update([
                        'status' => QRStatusEnum::CANCELLED,
                    ]);
                    $batchParent = $batchQrcode->reference;
                    $batchParent->status = ProductBatchStatusEnum::ORTHER;
                    $batchParent->quantity = 0;
                    $batchParent->save();
                    foreach ($batchQrcode?->reference?->productInBatch as $detail) {
                        $detail->update([
                            'batch_id' => $productBatch->id,
                        ]);
                        $detail->statusQrCode->update([
                            'status' => QRStatusEnum::PENDING,
                        ]);
                        $this->historiesQRcode($detail->statusQrCode);
                        $this->incrementQrScan($saleIssueDetail, $detail['product_id']);
                        ActualIssueQrCode::query()->create([
                            'issue_id' => $saleIssue->id,
                            'qrcode_id' => $detail->statusQrCode->id,
                            'batch_id' => $productBatch->id,
                            'is_batch' => 0,
                            'product_id' => $detail['product_id']
                        ]);
                    }
                }
                foreach ($products as $product) {
                    ActualIssueQrCode::query()->create([
                        'issue_id' => $saleIssue->id,
                        'qrcode_id' => $product['id'],
                        'batch_id' => $productBatch->id,
                        'is_batch' => 0,
                        'product_id' => $product['reference_id']
                    ]);
                    $this->productSave($product, $saleIssueDetail);
                    $productQrCode = ProductQrcode::find($product['id']);
                    $batchDetail = $productQrCode?->batchParent;
                    if ($batchDetail) {
                        $batchDetail->update([
                            'batch_id' => $productBatch->id,
                        ]);
                    } else {
                        $prd = Product::find($product['reference_id']);
                        $dataCreateBatchDetail = [
                            'batch_id' => $productBatch->id,
                            'product_id' => $product['reference_id'],
                            'qrcode_id' => $product['id'],
                            'product_name' => $prd->name,
                            'sku' => $prd->sku,
                        ];
                        ProductBatchDetail::query()->create($dataCreateBatchDetail);
                    }
                }
                DB::commit();
                return response()->json(['view' => view('plugins/hub-warehouse::product-batch-qrcode.qrcode-batch', compact('proBatch'))->render(), 'batch' => $productBatch, 'batchDetail' => $productBatch->productInBatch]);
            } else {
                foreach ($batchs as $batch) {
                    $batchQrcode = ProductQrcode::find($batch['id']);
                    $batchQrcode->update([
                        'status' => QRStatusEnum::PENDING,
                    ]);

                    ActualIssueQrCode::query()->create([
                        'issue_id' => $saleIssue->id,
                        'qrcode_id' => $batchQrcode->id,
                        'batch_id' => $batch['reference_id'],
                        'is_batch' => 1,
                    ]);
                    foreach ($batchQrcode?->reference?->productInBatch as $detail) {
                        $detail?->statusQrCode->update([
                            'status' => QRStatusEnum::PENDING,
                        ]);
                        $this->historiesQRcode($detail?->statusQrCode);
                        $this->incrementQrScan($saleIssueDetail, $detail['product_id']);
                        ActualIssueQrCode::query()->create([
                            'issue_id' => $saleIssue->id,
                            'qrcode_id' => $detail->statusQrCode->id,
                            'batch_id' => $batch['reference_id'],
                            'is_batch' => 0,
                            'product_id' => $detail['product_id']
                        ]);
                    }
                }
                foreach ($products as $product) {
                    $this->productSave($product, $saleIssueDetail);
                    $productQrCode = ProductQrcode::find($product['id']);
                    $batchDetail = $productQrCode?->batchParent;
                    if ($batchDetail) {
                        $batchDetail->delete();
                    }
                    ActualIssueQrCode::query()->create([
                        'issue_id' => $saleIssue->id,
                        'qrcode_id' => $product['id'],
                        'is_batch' => 0,
                        'batch_id' => 0,
                        'product_id' => $product['reference_id']
                    ]);
                }
                DB::commit();
                return response()->json(['batch' => $batchs, 'product' => $products]);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    private function historiesQRcode($productQrCode)
    {
        ProductQrHistotry::query()->create([
            'action' => 'receipt_stock',
            'created_by' => Auth::user()->id,
            'description' => 'Xác thực xuất kho sale thông qua việc quét mã QR của sản phẩm.',
            'qrcode_id' => $productQrCode->id,
        ]);
    }
    private function incrementQrScan($saleIssueDetail, $productId)
    {
        foreach ($saleIssueDetail as $detailIssue) {
            if ($detailIssue->product_id == $productId) {
                $detailIssue->quantity_scan += 1;
                $detailIssue->save();
            }
        }
    }
    private function productSave($product, $saleIssueDetail)
    {
        $productQrCode = ProductQrcode::find($product['id']);
        $productQrCode->update([
            'status' => QRStatusEnum::PENDING,
        ]);
        $batchDetail = $productQrCode?->batchParent?->productBatch;
        if ($batchDetail) {
            $batchDetail->quantity -= 1;
            $batchDetail->save();
        }
        $this->historiesQRcode($productQrCode);
        $this->incrementQrScan($saleIssueDetail, $product['reference_id']);
    }

    public function confirm($id, Request $request, BaseHttpResponse $response)
    {
        DB::beginTransaction();
        $saleIssue = SaleIssue::where('id', $id)->sharedLock()->first();


        $requestData = $request->input();
        if ($saleIssue->status == ProductIssueStatusEnum::PENDINGISSUE) {
            try {
                $saleIssue->proposal->update(['status' => ProposalIssueStatusEnum::CONFIRM]);
                $saleIssue->update([
                    'status' => ProductIssueStatusEnum::APPOROVED,
                    'invoice_confirm_name' => Auth::user()->name,
                    'date_confirm' => Carbon::now()
                ]);
                $processBatch = $this->processBatchIds($requestData, $response, $saleIssue->proposal);
                if (!$processBatch) {
                    return $this->responseError($response, 'Lỗi khi xuất lô');
                }
                $processQRcode = $this->processQrIds($requestData);
                if (!$processQRcode) {
                    return $this->responseError($response, 'Lỗi khi xuất lẻ');
                }
                $actualIssue = $this->createActualIssue($saleIssue, $requestData);
                $this->handleBatchProducts($requestData, $actualIssue);
                $this->handleProducts($requestData, $actualIssue);
                DB::commit();
                return $response
                    ->setPreviousUrl(route('sale-issue.view', $saleIssue->id))
                    ->setNextUrl(route('sale-issue.view', $saleIssue->id))
                    ->setMessage(trans('Thành công'));
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
                ->setMessage('Đơn đã được xác nhận');
        }
    }



    private function createActualIssue($productIssue, $requestData)
    {
        $filteredImages = array_filter($requestData['images']);
        $imageJson = json_encode($filteredImages);
        // Create the ActualIssue with the filtered and imploded images
        return SaleActualIssue::query()->create([
            'sale_issue_id' => $productIssue->id,
            'image' => $imageJson,
        ]);
    }
    private function handleBatchProducts($requestData, $actualIssue)
    {
        if (isset($requestData['batch_ids'])) {
            foreach ($requestData['batch_ids'] as $batch_id) {
                $batch = ProductBatch::find((int) $batch_id);
                if ($batch) {

                    $product = '';
                    $this->createActualIssueDetail($actualIssue, $batch, $product);
                }
            }
        }
    }
    private function handleProducts($requestData, $actualIssue)
    {
        if (isset($requestData['qr_ids'])) {
            foreach ($requestData['qr_ids'] as $qrId) {
                $qrCode = ProductQrcode::find($qrId);
                $product = Product::find($qrCode?->reference_id);
                list($color, $size) = $this->extractColorAndSize($product->variationProductAttributes);
                $this->createActualIssueDetail($actualIssue, null, $product);
            }

        }

    }
    private function createActualIssueDetail($actualIssue, $batch, $product)
    {
        if ($batch) {
            foreach ($batch->productInBatch as $batchDeatail) {
                $product = $batchDeatail->product;
                list($color, $size) = $this->extractColorAndSize($product->variationProductAttributes);
                $existingRecord = SaleActualIssueDetail::where([
                    'actual_id' => $actualIssue->id,
                    'product_id' => $product->id
                ])->first();
                if ($existingRecord) {
                    $existingRecord->update([
                        'quantity' => $existingRecord->quantity + 1,
                    ]);
                } else {
                    $actualIssueDetails = SaleActualIssueDetail::query()->create([
                        'actual_id' => $actualIssue->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'sku' => $product->sku,
                        'price' => $product->price,
                        'quantity' => 1,
                        'is_batch' => 1,
                        'batch_id' => $batch->id,
                        'color' => $color,
                        'size' => $size,
                    ]);
                }
            }
        } else {
            list($color, $size) = $this->extractColorAndSize($product->variationProductAttributes);
            $existingRecord = SaleActualIssueDetail::where([
                'actual_id' => $actualIssue->id,
                'product_id' => $product->id
            ])->first();
            if ($existingRecord) {
                $existingRecord->update([
                    'quantity' => $existingRecord->quantity + 1,
                ]);
            } else {
                $actualIssueDetails = SaleActualIssueDetail::query()->create([
                    'actual_id' => $actualIssue->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'quantity' => 1,
                    'is_batch' => 0,
                    'color' => $color,
                    'size' => $size,
                ]);
            }
        }
    }
    private function extractColorAndSize($arrAttribute)
    {
        $color = '';
        $size = '';

        foreach ($arrAttribute as $attribute) {
            if ($attribute->color) {
                $color = $attribute->title;
            } else {
                $size = $attribute->title;
            }
        }

        return [$color, $size];
    }
    public function processBatchIds($requestData, $response, $proposal)
    {
        DB::beginTransaction();
        try {
            if (isset($requestData['batch_ids'])) {
                foreach ($requestData['batch_ids'] as $batch_id) {
                    $batch = ProductBatch::find((int) $batch_id);
                    $batch->status = ProductBatchStatusEnum::OUTSTOCK;
                    $batch->save();

                    if ($batch->getQRCode) {
                        $qrCode = $batch->getQRCode;
                        $product = Product::find($batch->product_parent_id);
                        $this->updateProductAndQrStatus($product, $qrCode, QRStatusEnum::INTOUR);
                    }
                    $batchDetails = ProductBatchDetail::where('batch_id', (int) $batch_id)->get();
                    foreach ($batchDetails as $detail) {
                        $product = Product::find($detail->product_id);
                        if (!$product) {
                            throw new \Exception("Sản phẩm không tồn tại: " . $detail->product_id);
                        }

                        $quantityStockItem = SaleProduct::where([
                            'warehouse_id' => $proposal->warehouse_issue_id,
                            'product_id' => $detail->product_id
                        ])->first();

                        if (!$quantityStockItem) {
                            throw new \Exception("Thông tin tồn kho không tồn tại cho sản phẩm: " . $product->name);
                        }
                        $this->checkAndUpdateStock($product, $quantityStockItem);
                        $this->updateProductAndQrStatus($product, $detail->statusQrCode, QRStatusEnum::INTOUR);
                    }
                }
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }
    }

    private function processQrIds($requestData)
    {
        if (!isset($requestData['qr_ids'])) {
            return true;
        }
        DB::beginTransaction();
        try {
            foreach ($requestData['qr_ids'] as $qrId) {
                $qrCode = ProductQrcode::find($qrId);
                if (!$qrCode) {
                    throw new \Exception("Mã QR không tồn tại: $qrId");
                }
                $product = Product::find($qrCode->reference_id);
                if (!$product) {
                    throw new \Exception("Sản phẩm không tồn tại cho mã QR: $qrId");
                }
                $this->updateProductAndQrStatus($product, $qrCode, QRStatusEnum::INTOUR);

                $productStock = SaleProduct::where(['product_id' => $product->id, 'warehouse_id' => $qrCode->warehouse_id])->first();
                if (!$productStock) {
                    throw new \Exception("Thông tin tồn kho không tồn tại cho sản phẩm: " . $product->name);
                }
                $this->checkAndUpdateStock($product, $productStock);
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    private function checkAndUpdateStock($product, $quantityStockItem)
    {

        if ($quantityStockItem->quantity < 1) {
            throw new \Exception("Trong kho không còn sản phẩm $product->name");
        }
        $quantityStockItem->quantity--;
        $quantityStockItem->quantity_issue++;
        $quantityStockItem->save();
        return true;
    }

    protected function insertIssueInputTour($requestData)
    {
        $productQrcodeIds = $requestData['qr_ids'] ?? [];
        $id = (int) $requestData['product_issue'] ?? 0;

        $saleIssue = SaleIssue::find($id);
        if (!$saleIssue) {
            return ['message' => 'Phiếu không tồn tại.', 'error' => true];
        }

        if (empty($productQrcodeIds)) {
            return ['message' => 'Không có QR codes để xử lý.', 'error' => true];
        }

        $productQrcodes = ProductQrcode::whereIn('id', $productQrcodeIds)->get();

        if ($productQrcodes->isEmpty()) {
            return ['message' => 'QR codes không tồn tại.', 'error' => true];
        }

        $dataInsert = $productQrcodes->map(function ($productQrcode) use ($saleIssue) {
            return [
                'proposal_issues_id' => $saleIssue->proposal?->id,
                'qrcode_id' => $productQrcode->id,
                'where_type' => SaleWarehouse::class,
                'where_id' => $productQrcode->warehouse_id,
                'product_id' => $productQrcode->reference_id,
            ];
        })->toArray();

        IssueInputTour::query()->insert($dataInsert);
    }

    private function updateProductAndQrStatus($product, $qrCode, $qrCodeStatus)
    {
        $qrCode->status = $qrCodeStatus;
        $qrCode->save();
    }


    public function view($id, BaseHttpResponse $response)
    {
        $productIssue = SaleIssue::where('id', $id)->first();
        $ProposalIssuesId = $productIssue->proposal?->id;
        if ($productIssue->whereIn('warehouse_issue_id', get_list_sale_warehouse_id_for_current_user())->exists() || Auth::user()->hasPermission('sale-warehouse.all')) {
            $actualIssue = SaleActualIssue::where('sale_issue_id', $productIssue->id)->with('autualDetail')->first();
            $batchs = $productIssue->actualQrCode;
            $productInput = IssueInputTour::query()
                ->select('id','proposal_issues_id','qrcode_id','product_id')
                ->where('proposal_issues_id', $ProposalIssuesId)
                ->where('where_type', SaleWarehouse::class)
                ->with(['product'])
                ->get();
            PageTitle::setTitle(trans('Chi tiết phiếu xuất kho sale - :name', ['name' => BaseHelper::clean(get_proposal_issue_product_code($productIssue->issue_code))]));
            Assets::addScripts(['sortable'])
                ->addScriptsDirectly(
                    [
                        'vendor/core/plugins/gallery/js/gallery-admin.js',
                        'vendor/core/plugins/warehouse-finished-products/js/print-batch-qrcode.js',
                        "https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js",
                        'https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js'
                    ]
                );
            return view('plugins/sale-warehouse::sale-issue.view', compact('productIssue', 'actualIssue', 'batchs','productInput'));
        }
        ;
        return $response
            ->setError()
            ->setMessage('Không có quyền truy cập');
    }

    public function denied($id, Request $request, BaseHttpResponse $response)
    {
        $saleIssue = SaleIssue::where('id', $id)->sharedLock()->first();
        DB::beginTransaction();
        try {
            if ($saleIssue->status != ProductIssueStatusEnum::PENDING) {
                return $response
                    ->setError()
                    ->setMessage('Phiếu đã bị hủy');
            }
            $saleIssue->proposal->update(['status' => ProposalIssueStatusEnum::REFUSE, 'reason_cancel' => $request->input('denyReason')]);
            $saleIssue->update(['status' => ProductIssueStatusEnum::DENIED, 'reason_cancel' => $request->input('denyReason'), 'invoice_confirm_name' => Auth::user()->name]);

            $arrNoti = [
                'action' => 'từ chối',
                'permission' => "sale-proposal-issue.approve",
                'route' => route('sale-issue.view', $saleIssue->id),
                'status' => 'từ chối'
            ];
            send_notify_cms_and_tele($saleIssue, $arrNoti);
            DB::commit();
            return $response
                ->setPreviousUrl(route('sale-issue.view', $saleIssue->id))
                ->setNextUrl(route('sale-issue.view', $saleIssue->id))
                ->setError()
                ->setMessage('Từ chối xuất kho');
        } catch (Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }


    }

    public function confirmReceiptInTour(Request $request, BaseHttpResponse $response)
    {
        $result = $this->saleIssueRepository->confirmReceiptInTour($request->all());

        if (!$result['error']) {
            return $response->setMessage($result['message']);
        } else {
            return $response->setError()->setMessage($result['message']);
        }
    }
}
