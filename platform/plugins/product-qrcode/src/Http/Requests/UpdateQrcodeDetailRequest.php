<?php

namespace Botble\ProductQrcode\Http\Requests;
use Botble\Support\Http\Requests\Request;

class UpdateQrcodeDetailRequest extends Request
{
    public function rules()
    {
        return [
            'reason' => 'required|max:255',
        ];
    }
}
