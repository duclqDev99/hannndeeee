<?php

namespace Botble\ProductQrcode\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ProductQrcodeRequest extends Request
{
    public function rules(): array
    {
        return [
            'data.*.reference_id' => 'required|integer',
            'data.*.select_qty' => 'required|integer|min:1',
            'data.*.status' => 'required|in:created',
        ];
    }
}
