<?php

namespace Botble\Showroom\Http\Requests;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Support\Http\Requests\Request;

class ShowroomAddCustomerWhenCreateOrderRequest extends Request
{
    public function rules(): array 
    {
        return [
            'name' => 'required|string|max:60',
            'phone' => 'required|regex:/^0[0-9]{9,10}$/',
            'showroom_id' => 'required|integer|min:1|exists:showrooms,id',
            'vid' => 'required',
        ];
    }
}
