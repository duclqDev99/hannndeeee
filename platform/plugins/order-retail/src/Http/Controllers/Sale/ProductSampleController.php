<?php

namespace Botble\OrderRetail\Http\Controllers\Sale;

use Botble\Sales\Http\Requests\SalesRequest;
use Botble\Sales\Models\Sales;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\Sales\Tables\SalesTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Sales\Forms\SalesForm;
use Botble\Base\Forms\FormBuilder;
use Botble\Sales\Models\Product;

class ProductSampleController extends BaseController
{
    public function view(string|int $id)
    {
        PageTitle::setTitle(trans('plugins/sales::sales.create'));

        $product = Product::where('id', $id)->first();

        return view('plugins/sales::product.view', compact('product'));
    }

    public function update(Product $product, Request $request, BaseHttpResponse $response)
    {
        try{
            $product->fill($request->input());
            $product->save();
            event(new UpdatedContentEvent(CREATE_PRODUCT_WHEN_ORDER_MODULE_SCREEN_NAME, $request, $product));

            return $response
            ->setPreviousUrl(route('product-sample.view', $product->id))
            ->setMessage(trans('core/base::notices.update_success_message'));
        }catch(Exception $e){
            throw $e;
        }
    }
}
