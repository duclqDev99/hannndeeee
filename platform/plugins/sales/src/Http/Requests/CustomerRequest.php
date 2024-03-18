<?php

namespace Botble\Sales\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class CustomerRequest extends Request
{
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'gender' => 'string|max:25',
            'email' => 'required|string|max:255',
            'phone' => 'required|string|max:25',
            'address' => 'string|max:225',
            'level' => 'string|max:191',
            'status' => Rule::in(BaseStatusEnum::values()),
        ];
    }
}
