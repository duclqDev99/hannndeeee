<?php

namespace Botble\Warehouse\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class SupplierRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:220',
            // 'phone_number'=> ['regex:/^(\+84|0)(3[2-9]|5[689]|7[06789]|8[1-9]|9[0-9])[0-9]{7}$/'],
            'status' => Rule::in(BaseStatusEnum::values()),
        ];
    }
}
