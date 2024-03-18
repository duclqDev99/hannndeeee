<?php

namespace Botble\InventoryDiscountPolicy\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Http\Requests\DiscountRequest;
use Botble\Ecommerce\Models\Product;
use Botble\InventoryDiscountPolicy\Http\Requests\InventoryDiscountPolicyRequest;
use Botble\InventoryDiscountPolicy\Models\InventoryDiscountPolicy;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Media\Facades\RvMedia;
use Botble\Media\Models\MediaFile;
use Botble\Media\Services\UploadsManager;
use Botble\OrderRetail\Models\Contract;
use Botble\SaleWarehouse\Models\SaleWarehouse;
use Botble\Showroom\Models\Showroom;
use Carbon\Carbon;
use DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Exception;
use Botble\InventoryDiscountPolicy\Tables\InventoryDiscountPolicyTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\InventoryDiscountPolicy\Forms\InventoryDiscountPolicyForm;
use Botble\Base\Forms\FormBuilder;
use Botble\JsValidation\Facades\JsValidator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;


class InventoryDiscountPolicyController extends BaseController
{
    public function index(InventoryDiscountPolicyTable $table)
    {
        PageTitle::setTitle(trans('Danh sách'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('Tạo chính sách mới'));


        Assets::usingVueJS()
            ->addStylesDirectly('vendor/core/plugins/ecommerce/css/ecommerce.css')
            ->addScriptsDirectly('vendor/core/plugins/inventory-discount-policy/js/discount.js')
            ->addScripts(['timepicker', 'input-mask', 'form-validation'])
            ->addStyles('timepicker');

        $jsValidation = JsValidator::formRequest(InventoryDiscountPolicyRequest::class);
        return view('plugins/inventory-discount-policy::create', compact('jsValidation'));
    }

    public function store(InventoryDiscountPolicyRequest $request, BaseHttpResponse $response)
    {
        $requestData = ($request->input());
        $document = ($request->file('document'));
        $filteredImages = array_filter($requestData['images']);
        $imageJson = json_encode($filteredImages);
        $dateFormat = BaseHelper::getDateTimeFormat();
        $data = [
            'code' => $requestData['code'],
            'name' => $requestData['name'],
            'start_date' => Carbon::parse("{$requestData['start_date']} {$requestData['start_time']}")->format($dateFormat),
            'end_date' => $request->has('end_date') && !$request->has('unlimited_time') ? Carbon::parse("{$requestData['end_date']} {$requestData['end_time']}")->format($dateFormat) : null,
            'type_warehouse' => $requestData['type_warehouse'] == 'sale' ? SaleWarehouse::class : Showroom::class,
            'type_date_active' => $requestData['type_date_active'] ?? null,
            'time_active' => $requestData['time_active'] ?? null,
            'type_time' => $requestData['type_time'] ?? null,
            'quantity' => $requestData['quantity'] ?? 0,
            'type_option' => $requestData['type_option'],
            'value' => $requestData['value'],
            'status' => $requestData['status'],
            'target' => $requestData['target'],
            'document' => $document->getClientOriginalName(),
            'product_category_id' => $requestData['product_categories'] ?? null,
            'image' => $imageJson ?? null,
            'product' => $requestData['products'] ?? null,
            'customer_class_type' => $requestData['customer_class_type'] ?? null,
            'apply_for' => $requestData['apply_for'] ?? null,


        ];
        DB::beginTransaction();
        try {
            if ($document) {
                $saveFileResult = $this->saveProductFile($document, 'document');
                if (!Contract::create($saveFileResult)) {
                    $response->setError()->setMessage('Upload thất bại');
                }
                ;
            }

            $inventoryDiscountPolicy = InventoryDiscountPolicy::query()->create(
                $data
            );
            DB::commit();
            event(new CreatedContentEvent(INVENTORY_DISCOUNT_POLICY_MODULE_SCREEN_NAME, $request, $inventoryDiscountPolicy));

            return $response
                ->setNextUrl(route('inventory-discount-policy.index'))
                ->setMessage(trans('core/base::notices.create_success_message'));
        } catch (Exception $e) {
            DB::rollBack();
            dd($e);
        }

    }

    public function edit(InventoryDiscountPolicy $inventoryDiscountPolicy, FormBuilder $formBuilder)
    {
        $discount = $inventoryDiscountPolicy;
        $products = explode(',', $discount->product);
        $listProduct = [];

        foreach ($products as $product_id) {
            $product = Product::find($product_id);
            if ($product) {
                $listProduct[] = $product;
            }
        }
        $discount->listProduct = $listProduct;
        $images = json_decode($discount->image, true);

        $document =
        [
           'file' =>  RvMedia::url('document/'.$discount->document),
           'value' => $discount->document
        ];

        $url = [];
        foreach ($images as $image) {
            $url[] = [
                'file' => RvMedia::url($image),
                'value' => ($image),
            ];
        }

        $discount->url = $url;
        $discount->document = $document;

        PageTitle::setTitle(trans('Chỉnh sửa chính sách ":name"', ['name' => $inventoryDiscountPolicy->name]));
        Assets::usingVueJS()
            ->addStylesDirectly('vendor/core/plugins/ecommerce/css/ecommerce.css')
            ->addScriptsDirectly('vendor/core/plugins/inventory-discount-policy/js/discount.js')
            ->addScripts(['timepicker', 'input-mask', 'form-validation'])
            ->addStyles('timepicker');

        return view('plugins/inventory-discount-policy::edit', compact('discount'));
    }

    public function update(InventoryDiscountPolicy $inventoryDiscountPolicy, InventoryDiscountPolicyRequest $request, BaseHttpResponse $response)
    {
        $requestData = ($request->input());
        $document = $request->file('document');

        $filteredImages = array_filter($requestData['images']);
        $imageJson = json_encode($filteredImages);
        $dateFormat = BaseHelper::getDateTimeFormat();
        $data = [
            'code' => $requestData['code'],
            'name' => $requestData['name'],
            'start_date' => Carbon::parse("{$requestData['start_date']} {$requestData['start_time']}")->format($dateFormat),
            'end_date' => $request->has('end_date') && !$request->has('unlimited_time') ? Carbon::parse("{$requestData['end_date']} {$requestData['end_time']}")->format($dateFormat) : null,
            'type_warehouse' => $requestData['type_warehouse'] == 'sale' ? SaleWarehouse::class : Showroom::class,
            'type_date_active' => $requestData['type_date_active'] ?? null,
            'time_active' => $requestData['time_active'] ?? null,
            'type_time' => $requestData['type_time'] ?? null,
            'quantity' => $requestData['quantity'] ?? 0,
            'type_option' => $requestData['type_option'],
            'value' => $requestData['value'],
            'status' => $requestData['status'],
            'target' => $requestData['target'],
            'document' => $document? $document->getClientOriginalName() : $inventoryDiscountPolicy->document,
            'product_category_id' => $requestData['product_categories'] ?? null,
            'image' => $imageJson ?? null,
            'product' => $requestData['products'] ?? null,
            'customer_class_type' => $requestData['customer_class_type'] ?? null,
            'apply_for' => $requestData['apply_for'] ?? null,
        ];
        DB::beginTransaction();
        try {
            if ($document) {
                $saveFileResult = $this->saveProductFile($document, 'document');
                if (!Contract::create($saveFileResult)) {
                    $response->setError()->setMessage('Upload thất bại');
                }
                ;
            }
            $inventoryDiscountPolicy->fill($data);

            $inventoryDiscountPolicy->save();
            DB::commit();
            event(new UpdatedContentEvent(INVENTORY_DISCOUNT_POLICY_MODULE_SCREEN_NAME, $request, $inventoryDiscountPolicy));

            return $response
                ->setPreviousUrl(route('inventory-discount-policy.index'))
                ->setMessage(trans('core/base::notices.update_success_message'));
        } catch (Exception $e) {
            DB::rollBack();
            dd($e);
        }



    }

    public function destroy(InventoryDiscountPolicy $inventoryDiscountPolicy, Request $request, BaseHttpResponse $response)
    {
        try {
            $inventoryDiscountPolicy->delete();

            event(new DeletedContentEvent(INVENTORY_DISCOUNT_POLICY_MODULE_SCREEN_NAME, $request, $inventoryDiscountPolicy));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
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
}
