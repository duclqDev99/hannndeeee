<?php

namespace Botble\OrderRetail\Http\Controllers\Sale;

use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\OrderAnalysis\Tables\OrderAnalysisTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\OrderAnalysis\Forms\OrderAnalysisForm;
use Botble\Base\Forms\FormBuilder;
use Botble\Department\Enums\OrderDepartmentStatusEnum;
use Botble\Department\Models\OrderDepartment;
use Botble\Media\Models\MediaFile;
use Botble\Media\Services\UploadsManager;
use Botble\OrderAnalysis\Enums\OrderAttachStatusEnum;
use Botble\OrderAnalysis\Enums\OrderQuotationStatusEnum;
use Botble\OrderAnalysis\Http\Requests\OrderQuotationRequest;
use Botble\OrderAnalysis\Models\OrderAnalysis;
use Botble\OrderAnalysis\Models\OrderAttach;
use Botble\OrderAnalysis\Models\OrderQuatationDetail;
use Botble\OrderRetail\Forms\Sale\ProductionForm;
use Botble\OrderRetail\Models\OrderQuotation;
use Botble\OrderRetail\Forms\Sale\QuotationForm;
use Botble\OrderRetail\Models\Contract;
use Botble\OrderRetail\Tables\Sale\QuotationTable;
use Botble\OrderRetail\Models\Order;
use Botble\OrderRetail\Models\OrderProduct;
use Botble\OrderRetail\Models\OrderProduction;
use Botble\OrderRetail\Models\ProductDesignFile;
use Botble\OrderRetail\Models\ProductImageFile;
use Botble\OrderRetail\Tables\Sale\ProductionTable;
use Botble\OrderStepSetting\Enums\ActionEnum;
use Botble\OrderStepSetting\Enums\ActionStatusEnum;
use Botble\OrderStepSetting\Services\StepService;
use Botble\Warehouse\Models\Material;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ProductController extends BaseController
{


    public function getByOrder(Request $request)
    {
        $orderCode = $request->order_code;
        $products = OrderProduct::whereRelation('order', 'code', $orderCode)
            ->withSum('sizes as qty', 'quantity')
            ->get();
        return view('plugins/order-retail::product.list', compact('products'));
    }


    protected function saveProductFile(UploadedFile $file, $folderPath): array
    {
        $fileExtension = $file->getClientOriginalExtension();
        $content = File::get($file->getRealPath());
        $name = File::name($file->getClientOriginalName());
        $fileName = MediaFile::createSlug(
            $name,
            $fileExtension,
            Storage::path($folderPath)
        );

        $filePath = $folderPath . '/' . $fileName;
        app(UploadsManager::class)->saveFile($filePath, $content, $file);
        $data = app(UploadsManager::class)->fileDetails($filePath);
        $data['name'] = $name;
        $data['extension'] = $fileExtension;

        return [
            'url' => $filePath,
            'extras' => $data,
        ];
    }

    public function uploadContract(Request $request, StepService $stepService)
    {
        $result = $this->httpResponse();
        DB::beginTransaction();
        try {

            $file = $request->file('contract');
            if ($file) {
                $saveFileResult = $this->saveProductFile($file, 'contract');

                Arr::set($saveFileResult, 'quotation_id', $request->quotation_id);
                if (!Contract::create($saveFileResult)) {
                    $result->setError()->setMessage('Upload thất bại');
                };

                $quotation = OrderQuotation::with('order:id,code')->find($request->quotation_id);
                $order_id = $quotation->order->id;

                if ($order_id) {
                    $stepService->updateStep(ActionEnum::CUSTOMER_SIGN_CONTRACT, [
                        'order_id' => $order_id,
                        'status' => ActionStatusEnum::SIGNED,
                        'note' => null,
                        'type' => 'next',
                    ]);
                }
            }

            DB::commit();
            return $result->setMessage('Đã cập nhật 1 bản hợp đồng');
        } catch (\Exception $e) {
            DB::rollBack();
            return $result->setError()->setMessage($e->getMessage());
        }
    }

    public function edit(OrderProduct $product)
    {
        $product
            ->loadMissing('sizes')
            ->loadSum('sizes as qty', 'quantity');
        return view('plugins/order-retail::sale.product.edit', compact('product'));
    }

    public function update(Request $request)
    {
        $result = $this->httpResponse();
        $data = $request->input();
        $product_id = Arr::get($data, 'id', null);

        DB::beginTransaction();
        try {
            if ($product_id) {
                $product = OrderProduct::find($product_id);

                $product->update([
                    'sku' => $data['product_sku'],
                    'cal' => $data['product_cal'],
                    'product_name' => $data['product_name'],
                    'ingredient' => $data['ingredient'],
                    'price' => $data['product_price'],
                    'description' => $data['product_note'],
                    'address' => $data['product_address'],
                    'shipping_method' => $data['product_shipping_method'],
                ]);

                updateOrderTotalAmount($product->order_id);

                if (Arr::get($data, 'imagesDelete', null)) {
                    foreach ($data['imagesDelete'] as $url) {
                        $productDelete = ProductImageFile::where('url', $url)->where('retail_product_id', $product_id)->first();
                        if ($productDelete) $productDelete->delete();
                    }
                }

                if ($fileDesign = $request->file('file_design')) {
                    $saveFileResult = $this->saveProductFile($fileDesign, 'product-design-files');
                    Arr::set($saveFileResult, 'retail_product_id', $product_id);
                    ProductDesignFile::create($saveFileResult);
                    $product->fileDesign()->delete();
                }

                if ($fileImages = $request->file('images')) {
                    foreach ($fileImages as $fileImage) {
                        $saveImagesFileResult = $this->saveProductFile($fileImage, 'product-image-files');
                        Arr::set($saveImagesFileResult, 'retail_product_id', $product_id);
                        ProductImageFile::create($saveImagesFileResult);
                    }
                }

                DB::commit();
                return $result->setMessage('Cập nhật thành công');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $result->setError()->setMessage($e->getMessage());
        }
    }
}
