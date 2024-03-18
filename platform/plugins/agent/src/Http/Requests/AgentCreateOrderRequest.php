<?php

namespace Botble\Agent\Http\Requests;

use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class AgentCreateOrderRequest extends Request
{
    public function rules(): array
    {
        $rules = [
            'customer_id' => 'nullable|exists:ec_customers,id',
        ];

        if (is_plugin_active('payment')) {
            $rules['payment_status'] = Rule::in([PaymentStatusEnum::COMPLETED, PaymentStatusEnum::PENDING]);
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'customer_id' => trans('plugins/ecommerce::order.customer_label'),
            'customer_address.phone' => trans('plugins/ecommerce::order.phone'),
        ];
    }
}
