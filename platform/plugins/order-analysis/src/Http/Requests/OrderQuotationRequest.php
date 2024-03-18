<?php

namespace Botble\OrderAnalysis\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class OrderQuotationRequest extends Request
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'order_id' => 'required',
            'quotation.*' => 'required',
            'effective_time' => 'required|date|after:now',
            'effective_payment' => 'required|date|after:effective_time',
            'transport_costs' => 'nullable|numeric|min:1',
        ];
    }
}
