<?php

namespace Botble\OrderRetail\Http\Controllers\Accountant;

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
use Botble\OrderRetail\Tables\Accountant\QuotationTable;
use Botble\OrderRetail\Models\Order;
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

class QuotationController extends BaseController
{
    public function index(QuotationTable $table)
    {
        PageTitle::setTitle('Kế toán | Quản lý báo giá');
        Assets::addScriptsDirectly([
            'vendor/core/plugins/order-retail/js/upload-contract.js',
            'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js'
        ]);
        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        $this->pageTitle('Tạo báo giá');

        $form = $formBuilder->create(QuotationForm::class);
        return view('plugins/order-retail::form.create-quotation', compact('form'));
    }

    public function show(OrderQuotation $quotation){
        abort_if(!$quotation, 404, 'page not found');
        $this->pageTitle('Thông tin báo giá | '. $quotation->order->code);
        return view('plugins/order-retail::accountant.quotation.show', compact('quotation'));
    }
}
