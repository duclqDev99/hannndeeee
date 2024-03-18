<?php

namespace Botble\Showroom\Http\Requests;

use Botble\Ecommerce\Models\Product;
use Botble\Showroom\Models\ShowroomWarehouse;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Validator;

class CheckOnScanProductRequest extends Request
{
    public function rules()
    {
        return [
            'status' => 'required|in:instock',
            'warehouse_type' => 'required',
            'warehouse_id' => 'required|numeric',
            'product_id' => 'required|numeric',
            'showroom_id' => 'required|numeric',
            'reference_type' => 'required',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $check = ShowroomWarehouse::where('id', $this->input('warehouse_id'))->where('showroom_id',$this->input('showroom_id'))->count();
        $validator->after(function ($validator) use ($check) {
            if ($this->input('warehouse_type') !== ShowroomWarehouse::class || $check == 0) {
                $validator->errors()->add('warehouse_type', 'Sản phẩm không thuộc showroom này.');
            }
            if ($this->input('reference_type') !== Product::class) {
                $validator->errors()->add('reference_type', 'Mã không thuộc của sản phẩm.');
            }
        });
    }

    public function messages()
    {
        return [
            'status.required' => 'Dữ liệu sản phẩm không hợp lệ.',
            'status.in' => 'Dữ liệu Sản phẩm không hợp lệ.',
            'warehouse_type.required' => 'Dữ liệu sản phẩm không hợp lệ.',
            'warehouse_id.required' => 'Dữ liệu sản phẩm không hợp lệ.',
            'product_id.required' => 'Dữ liệu sản phẩm không hợp lệ.',
            'showroom_id.required' => 'Dữ liệu sản phẩm không hợp lệ.',
            // Tương tự cho các trường khác...
        ];
    }
}
