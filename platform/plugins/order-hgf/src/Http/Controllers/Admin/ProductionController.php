<?php

namespace Botble\OrderHgf\Http\Controllers\Admin;

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
use Botble\OrderRetail\Models\OrderProduction;
use Botble\OrderHgf\Tables\Admin\ProductionTable;
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

class ProductionController extends BaseController
{
    public function index(ProductionTable $table)
    {
        PageTitle::setTitle('HGF | Đơn đặt hàng');
        return $table->renderTable();
    }

    public function show(OrderProduction $production){
        abort_if(!$production, 404, 'page not found');
        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
        ->addScriptsDirectly([
            'vendor/core/plugins/order-retail/js/edit-product.js',
        ])
        ->addScripts(['input-mask']);

        return view('plugins/order-hgf::admin.production.show', compact('production'));
    }

    public function create(FormBuilder $formBuilder)
    {
        $this->pageTitle('Tạo đơn đặt hàng');

        $form = $formBuilder->create(ProductionForm::class);
        return view('plugins/order-retail::form.create-production', compact('form'));
    }
    

    public function store(Request $request, StepService $stepService, BaseHttpResponse $response)
    {
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $order = Order::where('code', $requestData['order_code'])->first();
            Arr::set($requestData, 'quotation_id', $order->quotation->id);
            Arr::set($requestData, 'created_by_id', Auth::id());
            Arr::set($requestData, 'note', $requestData['note'] ?? null);

            $production = OrderProduction::create($requestData);

            $stepService->updateStep(ActionEnum::RETAIL_SALE_CREATE_PRODUCTION, [
                'order_id' => $order->id,
                'status' => ActionStatusEnum::CREATED,
                'note' => Arr::get($requestData, 'note', null),
                'type' => 'next',
            ]);

            DB::commit();
            return $response
                ->setNextUrl(route('retail.sale.production.index'))
                ->setMessage('Tạo đơn đặt hàng thành công');
        } catch (Exception $err) {
            DB::rollBack();
            dd($err);
            return $response->setError()->setMessage($err->getMessage());
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

                if($order_id){
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
}
