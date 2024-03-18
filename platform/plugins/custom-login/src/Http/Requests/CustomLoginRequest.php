<?php

namespace Botble\CustomLogin\Http\Requests;

use Botble\Support\Http\Requests\Request;

class CustomLoginRequest extends Request
{
    public function rules(): array
    {
        return [
            'phone' => 'numeric|regex:/^([0-9\s\-\+\(\)]*)$/|digits:10',

        ];
    }
}
