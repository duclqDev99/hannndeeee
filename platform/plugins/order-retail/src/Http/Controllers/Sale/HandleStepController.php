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
use Botble\Sales\Models\Order;
use Botble\Sales\Models\OrderDetail;
use Botble\Sales\Models\OrderHistory;
use Botble\Sales\Models\OrderReferenceProcedure;
use Botble\Sales\Models\Product as ProductOfSale;
use Botble\Sales\Models\Step;
use Botble\Sales\Models\StepInfo;
use Botble\Sales\Tables\CustomerTable;
use Botble\Sales\Supports\PurchaseOrderHelper;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HandleStepController extends BaseController
{
    public function updateStep(Request $request)
    {
        $stepDetail = get_action($request->action_code, $request->order_id);
        $stepDetail->update([
            "status" => $request->status,
            "note" => $request->note ?? "",
            "handler_id" => auth()->user()->id,
            "handled_at" => now(),
        ]);

        $a = [];
        if($stepDetail->update_relate_actions){
            foreach( $stepDetail->update_relate_actions as $type => $actions){
                if($type == $request->type && is_array($actions)){
                    foreach($actions as $action_code => $status){
                        $step = get_action($action_code, $request->order_id);
                        $step->update(['status' => $status]);
                    }
                }
            }
        }
     
        return response()->json([
            'success' => true,
            'update_relate_actions' => $stepDetail->update_relate_actions,
            'a' => $a
        ], 200);
    }
}
