<?php

namespace Botble\OrderAnalysis\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class OrderAnalysisRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'description' => 'max:400',
            'quantityAndId.*' => 'required|numeric|min:1',
        ];
    }
}
