<?php

namespace Botble\Sales\Http\Controllers;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Forms\FormBuilder;
use Botble\Department\Models\OrderDepartment;
use Botble\Ecommerce\Cart\CartItem;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\Discount;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\InvoiceHelper;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Http\Resources\AvailableProductResource;
use Botble\Ecommerce\Http\Resources\CartItemResource;
use Botble\Ecommerce\Models\Product;
use Botble\Sales\Models\Product as SaleProduct;
use Botble\Ecommerce\Services\HandleApplyCouponService;
use Botble\Ecommerce\Services\HandleApplyPromotionsService;
use Botble\Ecommerce\Services\HandleShippingFeeService;
use Botble\Ecommerce\Services\HandleTaxService;
use Botble\OrderHgf\Noti\HGFNoti;
use Botble\ProcedureOrder\Models\ProcedureOrder;
use Botble\Sales\Enums\OrderStatusEnum;
use Botble\Sales\Enums\OrderStepStatusEnum;
use Botble\Sales\Enums\TypeOrderEnum;
use Botble\Sales\Tables\OrderTable;
use Botble\Sales\Forms\CustomerForm;
use Botble\Sales\Http\Requests\CreateProductRequest;
use Botble\Sales\Http\Requests\CustomerRequest;
use Botble\Sales\Http\Resources\SampleProductResource;
use Botble\Sales\Models\Customer;
use Botble\OrderRetail\Models\Order;
use Botble\OrderStepSetting\Enums\ActionStatusEnum;
use Botble\Sales\Models\OrderDetail;
use Botble\Sales\Models\OrderHistory;
use Botble\Sales\Models\OrderReferenceProcedure;
use Botble\Sales\Models\Product as ProductOfSale;
use Botble\OrderStepSetting\Models\Step;
use Botble\OrderStepSetting\Services\StepService;
use Botble\Sales\Models\StepInfo;
use Botble\Sales\Tables\CustomerTable;
use Botble\Sales\Supports\PurchaseOrderHelper;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HandleStepController extends BaseController
{
    public function updateStep(Request $request, StepService $stepService)
    {
       
        try {
            $success = $stepService->updateStep($request->action_code, [
                'order_id' => $request->order_id,
                'status' => $request->status,
                'type' => $request->type,
                'note' => $request->note ?? null
            ]);

            if (!$success) throw new \Exception('Có lỗi sảy ra. vui lòng thử lại sau');

            return response()->json([
                'success' => true,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
