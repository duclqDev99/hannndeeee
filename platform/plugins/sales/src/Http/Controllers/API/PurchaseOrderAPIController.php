<?php

namespace Botble\Sales\Http\Controllers\API;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Sales\Models\Customer;
use Botble\Sales\Models\Order;
use Illuminate\Http\Request;

class PurchaseOrderAPIController extends BaseController
{
    public function getListCustomers (Request $request)
    {
        $customers = Customer::query()
        ->where('email', 'LIKE', '%' . $request->input('keyword') . '%')
        ->simplePaginate(20);

        foreach ($customers as &$customer) {
            $customer->avatar = (string)$customer->avatar;
        }

        return $this
            ->httpResponse()->setData($customers);
    }

    public function getListPurchaseOrder()
    {
        $orders = Order::query()->get();

        return $this
            ->httpResponse()->setData($orders);
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
