<?php

namespace Botble\Agent\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class CreateAgentProductRequest extends Request
{
    public function rules(): array
    {
        return [
            'products' => 'required|array',
            'products.*.product_id' => 'required',
            'products.*.select_qty' => 'required|numeric|min:1',
        ];
    }
}
