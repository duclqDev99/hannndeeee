<?php

namespace Botble\Showroom\Http\Controllers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Customer;
use Botble\SharedModule\Trait\CustomersAppTrait;
use Botble\Showroom\Http\Requests\ShowroomAddCustomerWhenCreateOrderRequest;
use Botble\Showroom\Models\Showroom;
use Botble\Showroom\Models\ShowroomCustomer;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShowroomCustomerController extends BaseController
{
    use CustomersAppTrait;
    public function store(ShowroomAddCustomerWhenCreateOrderRequest $request)
    {
        DB::beginTransaction();
        try{
            $customerData = $request->input(); // Dữ liệu từ request
            $showroomId = $request->showroom_id;

            // Cập nhật hoặc tạo mới Customer
            $customer = Customer::updateOrCreate(
                ['phone' => $customerData['phone']], // Điều kiện tìm kiếm
                $customerData // Dữ liệu cần cập nhật hoặc tạo mới
            );

            $customer->confirmed_at = $customer->created_at;
            $customer->save();
            // Cập nhật hoặc tạo mới ShowroomCustomer
            ShowroomCustomer::updateOrCreate(
                ['customer_id' => $customer->id, 'where_type' => Showroom::class, 'where_id' => $showroomId], // Điều kiện tìm kiếm
                ['customer_id' => $customer->id, 'where_type' => Showroom::class, 'where_id' => $showroomId] // Dữ liệu cần cập nhật hoặc tạo mới
            );
            event(new CreatedContentEvent(CUSTOMER_MODULE_SCREEN_NAME, $request, $customer));

            DB::commit();
            return $this
                ->httpResponse()
                ->setData(compact('customer'))
                ->withCreatedSuccessMessage();
        } catch (Exception $e) {
            DB::rollBack();
            return $this
                ->setError()
                ->setMessage(trans('Thêm khách hàng thất bại!!!'));
        }
    }

    public function checkUserRegisterApp(Request $request)
    {
        $messages = [
            'phone_customer.required' => 'Trường số điện thoại là bắt buộc.',
            'phone_customer.regex' => 'Số điện thoại không hợp lệ.',
        ];
        $validatedData = $request->validate([
            'phone_customer' => 'required|regex:/^\+?([0-9\s\-\(\)])*$/',
        ], $messages);
        $responseData = $this->getInfoCustomerApi($request->phone_customer);
        if ($responseData['error_code'] == 0) {
            $dataRes = [
                'error_code' => 0,
                'msg' => 'Thành công',
                'data' => $responseData['data'],
            ];
            return response()->json($dataRes, 200);
        }
        $exception = new \Exception('Lỗi khi lấy thông tin khách hàng từ app:' . $responseData['error_msg']);
        report($exception);
        $dataRes = [
            'error_code' => 1,
            'msg' => 'Lỗi',
            'data' => $responseData['error_msg'],
        ];
        return response()->json($dataRes, 200);

    }

    public function getListCustomerForSearch(Request $request)
    {
        $customers = Customer::query()
            ->orWhere('phone', 'LIKE', '%' . $request->input('keyword') . '%')
            ->simplePaginate(5);

        foreach ($customers as &$customer) {
            $customer->avatar_url = (string)$customer->avatar_url;
        }

        return $this
            ->httpResponse()->setData($customers);
    }
    public function getCustomerOrderNumbers($id)
    {
        $customer = Customer::query()->find($id);

        if (! $customer) {
            return $this
                ->httpResponse()
                ->setData(0);
        }
        $responseData = $this->getInfoCustomerApi($customer->phone);
        if ($responseData['error_code'] == 0) {
            // Cập nhật Customer
            $customer->update([
                'name' => $responseData['data']['fullname'],
                'vid' => $responseData['data']['vga'],
            ]);
            return $this
            ->httpResponse()
            ->setData([
                'customer' => $customer,
                'order_numbers' => $customer->orders()->count()
            ]
            );
        }
        $exception = new \Exception('Lỗi khi lấy thông tin khách hàng từ app:' . $responseData['error_msg']);
        report($exception);
        $dataRes = [
            'error_code' => 1,
            'msg' => 'Lỗi',
            'data' => $responseData['error_msg'],
        ];
        return response()->json($dataRes, 200);

    }

}
