<?php

namespace Botble\Sales\Http\Requests;

use Botble\Support\Http\Requests\Request;

class CreateProductRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:220',
            'sku' => 'required|string|max:120',
            'price' => 'numeric|required|min:1',
            'color' => 'nullable|string|max:60',
            'size' => 'nullable|string|max:10',
            'ingredient' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => "Tên sản phẩm không được để trống!!",
            'name.max' => "Tên sản phẩm vượt quá độ dài cho phép!!",
            'sku.required' => "Mã sản phẩm không được để trống!!",
            'sku.max' => "Mã sản phẩm vượt quá độ dài cho phép.!!",
            'price.required' => "Giá dự kiến không được để trống!!",
            'price.min' => "Giá dự kiến phải lớn hơn :min!!",
            'color.max' => "Màu sắc vượt quá độ dài cho phép. Tối đa :max ký tự!!",
            'size.max' => "Kích thước vượt quá độ dài cho phép!!",
        ];
    }
}
