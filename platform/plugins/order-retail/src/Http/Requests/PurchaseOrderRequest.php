<?php

namespace Botble\OrderRetail\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\OrderRetail\Enums\OrderTypeEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class PurchaseOrderRequest extends Request
{
    public function rules(): array
    {
        return [
            'customer_name' => 'required|max:255',
            'customer_phone' => 'required|min:9',
            'expected_date' => 'required|date|after:today',
            'order_type' => 'required|' . Rule::in(OrderTypeEnum::values()),
            'products' => 'required|array|min:1',
            'products.*.sizes' => 'required|array|min:1',
        ];
    }

    public function messages()
    {
        return [
            'customer_name.required' => 'Vui lòng nhập tên khách hàng',
            'customer_phone.required' => 'Vui lòng nhập số điện thoại khách hàng',
            'customer_phone.min' => 'Số điện thoại phải ít nhất 9 số',
            'expected_date.required' => 'Vui lòng nhập ngày cần hàng',
            'expected_date.after' => 'Ngày cần hàng phải sau ngày hôm nay',
            'products.min' => 'Vui lòng nhập ít nhất 1 thông tin mặt hàng',
            'products.*.sizes.required' => 'Vùi lòng thêm size cho mặt hàng',
        ];
    }
}
