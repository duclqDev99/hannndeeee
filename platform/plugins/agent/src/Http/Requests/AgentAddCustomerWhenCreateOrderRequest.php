<?php

namespace Botble\Agent\Http\Requests;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Support\Http\Requests\Request;

class AgentAddCustomerWhenCreateOrderRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:60',
            'phone' => 'required|regex:/^0[0-9]{9,10}$/',
            'agent_id' => 'required|integer|min:1|exists:agents,id',
            'vid' => 'required',
        ];
    }
}
