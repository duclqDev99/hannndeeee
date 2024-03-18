<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Ecommerce\Models\Product;
use Botble\Support\Http\Requests\Request;

class AddAttributesToProductRequest extends Request
{
    public function rules(): array
    {
        $product = Product::query()->findOrFail(request()->id);
        request()->merge([
            'sku' => $product?->sku,
            'weight' => $product?->weight,
            'price' => $product?->price,
            'ingredient' => $product?->ingredient,
        ]);
        $rules = [
            'added_attributes' => 'sometimes|array',
            'added_attribute_sets' => 'sometimes|array',
        ];

        // Kiểm tra nếu sản phẩm không có các trường này thì thêm quy tắc 'required'
        if (is_null($product->sku)) {
            $rules['sku'] = 'required';
        }

        if (is_null($product->weight)) {
            $rules['weight'] = 'required';
        }

        if (is_null($product->price)) {
            $rules['price'] = 'required';
        }

        if (is_null($product->ingredient)) {
            $rules['ingredient'] = 'required';
        }

        return $rules;
    }
}
