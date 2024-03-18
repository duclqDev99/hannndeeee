<?php

namespace Botble\Warehouse\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class DeniRequest extends Request
{
    public function rules(): array
    {
        return [
            'denyReason' =>'required',
        ];
    }
}
