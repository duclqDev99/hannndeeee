<?php

namespace Botble\Sales\Http\Controllers\API;

use Botble\Base\Http\Controllers\BaseController;
use Botble\OrderAnalysis\Models\OrderAttach;
use Botble\Sales\Models\Customer;
use Botble\Sales\Models\Order;
use Illuminate\Http\Request;

class OrderAttachAPIController extends BaseController
{
    public function getInformationProductAttach (Request $request)
    {
        $orderAttach = OrderAttach::where([
            'order_id' => $request->input()['orderId'],
            'attach_type' => $request->input()['type'],
            'attach_id' => $request->input()['id'],
        ])->first();

        if(!empty($orderAttach->attachFile)){
            $data = [
                'owner' => $orderAttach->attachFile->createdBy,
                'context' => $orderAttach->attachFile
            ];
            return $this->responseApi(0, "Thành công!!", 200, $data);
        }else{
            return $this->responseApi(1, "Không tìm thấy bất kỳ file thiết kế nào!!", 200);
        }
    }

    public function responseApi($errorCode, $msg, $httpCode, $data = null)
    {
        $dataRes = [
            'error_code' => $errorCode,
            'msg' => $msg,
            'body' => $data,
        ];

        return response()->json($dataRes, $httpCode);
    }
}
