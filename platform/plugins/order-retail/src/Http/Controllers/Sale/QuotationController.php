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
use Botble\OrderRetail\Models\OrderQuotation;
use Botble\OrderRetail\Forms\Sale\QuotationForm;
use Botble\OrderRetail\Models\Contract;
use Botble\OrderRetail\Tables\Sale\QuotationTable;
use Botble\OrderRetail\Models\Order;
use Botble\OrderRetail\Models\OrderProduct;
use Botble\OrderRetail\Supports\OrderRetailHelper;
use Botble\OrderRetail\Supports\QuotationHelper;
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
use Illuminate\Support\Facades\Validator;

class QuotationController extends BaseController
{
    public function index(QuotationTable $table)
    {
        PageTitle::setTitle('Quản lý báo giá');
        Assets::addScriptsDirectly([
            'vendor/core/plugins/order-retail/js/upload-contract.js',
            'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js'
        ]);
        return $table->renderTable();
    }

    public function show(OrderQuotation $quotation)
    {
        abort_if(!$quotation, 404, 'page not found');
        $this->pageTitle('Thông tin báo giá | ' . $quotation->order->code);
        return view('plugins/order-retail::sale.quotation.show', compact('quotation'));
    }

    public function create(FormBuilder $formBuilder)
    {
        $this->pageTitle('Tạo báo giá');
        Assets::addScriptsDirectly([
            'vendor/core/plugins/order-retail/js/search-purchase-order.js',
            'vendor/core/plugins/order-retail/js/create-quotation.js',
            'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js'
        ]);
        $form = $formBuilder->create(QuotationForm::class);
        return view('plugins/order-retail::form.create-quotation', compact('form'));
    }

    public function store(Request $request, StepService $stepService, BaseHttpResponse $response)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'order_code' => 'required',
            'start_date' => 'required',
            'due_date' => 'required',
            // 'purchase_order_amount' => 'required',
            // 'quotation_amount'=> 'required|gt:purchase_order_amount',
            'start_date' => 'required|date|after:today',
            'due_date' => 'required|date|after:start_date',
            'products' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->httpResponse()->setError()->setMessage($validator->errors()->first());
        }

        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $order = Order::where('code', $requestData['order_code'])->first();
            // Arr::set($requestData, 'start_date', date_format(date_create_from_format('d-m-Y', $requestData['start_date']), 'Y-m-d'));
            Arr::set($requestData, 'created_by_id', Auth::id());
            $totalAmount = 0;
            $products = $requestData['products'];

            foreach ($products as $id => $values) {
                $price = $values['price'];
                $quotation_price = $values['quotation_price'];
                if ($quotation_price < $price) throw new \Exception('Vui lòng nhập giá bán lớn hơn giá sản xuất');
                $product =  OrderProduct::find($id);
                $product->update(['quotation_price' => $quotation_price]);
                $totalAmount += $product->quotation_price * $product->qty;
            }
            Arr::set($requestData, 'amount', $totalAmount);

            $quotation = OrderQuotation::create($requestData);


            $stepService->updateStep(ActionEnum::RETAIL_SALE_CREATE_QUOTATION, [
                'order_id' => $order->id,
                'status' => ActionStatusEnum::CREATED,
                'note' => Arr::get($requestData, 'note', null),
                'type' => 'next',
            ]);

            DB::commit();
            return $response
                ->setNextUrl(route('retail.sale.quotation.index'))
                ->setMessage('Tạo phiếu báo giá thành công');
        } catch (Exception $err) {
            DB::rollBack();
            return $response->setError()->setMessage($err->getMessage());
        }
    }

    public function edit(OrderQuotation $quotation, FormBuilder $formBuilder)
    {
        abort_if(!$quotation, 404, 'page not found');

        $this->pageTitle('Chỉnh sửa báo giá');
        Assets::addScriptsDirectly([
            'vendor/core/plugins/order-retail/js/search-purchase-order.js',
            'vendor/core/plugins/order-retail/js/create-quotation.js',
            'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js'
        ]);
        $form = QuotationForm::createFromModel($quotation)->setUrl(route('retail.sale.quotation.update', $quotation->id));
        return view('plugins/order-retail::form.create-quotation', compact('form'));
    }

    public function update(OrderQuotation $quotation, Request $request)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'order_code' => 'required',
            'start_date' => 'required',
            'due_date' => 'required',
            // 'purchase_order_amount' => 'required',
            // 'quotation_amount' => 'required|gt:purchase_order_amount',
            'products' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->httpResponse()->setError()->setMessage($validator->errors()->first());
        }

        $totalAmount = 0;
        $products = $request->products ?? [];


        foreach ($products as $id => $values) {
            $price = $values['price'];
            $quotation_price = $values['quotation_price'];
            if ($quotation_price < $price)  return $this->httpResponse()->setError()->setMessage('Vui lòng nhập giá bán lớn hơn giá sản xuất!');
            $product =  OrderProduct::find($id);
            $product->update(['quotation_price' => $quotation_price]);
            $totalAmount += $product->quotation_price * $product->qty;
        }

        $data = $request->input();
        Arr::set($data, 'amount',  $totalAmount);
        $quotation->update($data);

        return $this
        ->httpResponse()
        ->setNextUrl(route('retail.sale.quotation.index'))
        ->setMessage('Cập nhật thành công');
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

    public function getQuotationInvoice(OrderQuotation $quotation, Request $request, QuotationHelper $quotationHelper)
    {
        abort_if(!$quotation, 404, 'page not found');

        if ($request->input('type') == 'print') {
            return $quotationHelper->streamInvoice($quotation);
        }

        return $quotationHelper->downloadInvoice($quotation);
    }
}
