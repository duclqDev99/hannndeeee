<?php

namespace Botble\ProductQrcode\Http\Controllers;

use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\ProductQrcode\Http\Requests\UpdateQrcodeDetailRequest;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\ProductQrcode\Tables\ProductQrcodeDetaiTable;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;




class QrcodeDetailController extends BaseController
{
    public function __construct()
    {

    }

    public function detail(ProductQrcodeDetaiTable $table)
    {
        Assets::addScriptsDirectly([
            'vendor/core/plugins/product-qrcode/js/script.js',
        ]);
        // dd($table);
        PageTitle::setTitle(trans('plugins/product-qrcode::product-qrcode.name'));

        return $table->render('plugins/product-qrcode::table/product-qrcode');
    }
    public function updateStatusQRCodeDetail(UpdateQrcodeDetailRequest $req, BaseHttpResponse $response)
    {
        $dataForm = $req->all();
        DB::beginTransaction();
        try{
            $productQrcode = ProductQrcode::find($dataForm['id']);
            $productQrcode->status = 'cancelled';
            $productQrcode->reason = $dataForm['reason'];
            $productQrcode->save();
            DB::commit();
            event(new UpdatedContentEvent(PRODUCT_QRCODE_MODULE_SCREEN_NAME, $req, $productQrcode));

            return $response
                ->setPreviousUrl(route('product-qrcode.detail', $dataForm['times_product_id'] ))
                ->setMessage(trans('core/base::notices.update_success_message'));
        }catch(Exception $e){
            DB::rollBack();
            throw new Exception('PRODUCT_QRCODE_MODULE_SCREEN_NAME:' . $e->getMessage());
        }
    }


}
