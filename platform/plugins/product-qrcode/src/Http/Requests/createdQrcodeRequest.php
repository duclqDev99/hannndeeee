<?php

namespace Botble\ProductQrcode\Http\Requests;
use Botble\Support\Http\Requests\Request;

class createdQrcodeRequest extends Request
{
    public function rules()
    {
        return [
            'data.description' => 'max:255',
            'data.title' => 'required|max:255',
            'data.products' => 'required|array',
            'data.products.*.product_id' => 'required|numeric',
            'data.products.*.select_qty' => 'required|integer|min:1',
        ];
    }
    public function messages()
    {
        return [
            'data.description.max' => 'Mô tả không được vượt quá 255 ký tự.',
            'data.title.required' => 'Tiêu đề là trường bắt buộc.',
            'data.title.max' => 'Tiêu đề không được vượt quá 255 ký tự.',
            'data.products.required' => 'Sản phẩm là trường bắt buộc.',
            'data.products.*.product_id.required' => 'ID sản phẩm là trường bắt buộc.',
            'data.products.*.product_id.numeric' => 'ID sản phẩm phải là một số.',
            'data.products.*.select_qty.required' => 'Số lượng chọn là trường bắt buộc.',
            'data.products.*.select_qty.integer' => 'Số lượng chọn phải là một số nguyên.',
            'data.products.*.select_qty.min' => 'Số lượng chọn phải lớn hơn hoặc bằng 0.',
        ];
    }
}
