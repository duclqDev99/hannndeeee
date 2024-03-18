<?php

namespace Botble\Showroom\Http\Requests;

use Botble\Support\Http\Requests\Request;

class CreateShowroomProductRequest extends Request
{
    public function rules(): array
    {
        return [
            'products' => 'required|array',
            'products.*.product_id' => 'required',
            'products.*.select_qty' => 'required|numeric|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'products.required' => 'Danh sách sản phẩm là bắt buộc.',
            'products.array' => 'Danh sách sản phẩm phải là một mảng.',
            'products.*.product_id.required' => 'ID sản phẩm là bắt buộc trong mỗi mục sản phẩm.',
            'products.*.select_qty.required' => 'Số lượng là bắt buộc trong mỗi mục sản phẩm.',
            'products.*.select_qty.numeric' => 'Số lượng phải là một số trong mỗi mục sản phẩm.',
            'products.*.select_qty.min' => 'Số lượng phải ít nhất là 1 trong mỗi mục sản phẩm.',
        ];
    }
}
