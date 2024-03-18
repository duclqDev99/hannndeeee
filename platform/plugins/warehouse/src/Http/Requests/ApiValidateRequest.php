<?php
namespace Botble\Warehouse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApiValidateRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Cho phép tất cả mọi người sử dụng request này
    }

    public function rules()
    {
        return [
            'material.*.quantity' => 'required',
        ];
    }
}
