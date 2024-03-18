<?php

namespace Botble\Warehouse\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class MaterialPlanRequest extends Request
{
    public function prepareForValidation()
    {
        if ($this->has('quantity') && is_array($this->input('quantity'))) {
            $quantity = $this->input('quantity');
            array_shift($quantity);
            $this->merge([
                'quantity' => $quantity,
            ]);
        }
        if ($this->has('price') && is_array($this->input('price'))) {
            $price = $this->input('price');
            array_shift($price);
            $this->merge([
                'price' => $price,
            ]);
        }
        if ($this->has('material') && is_array($this->input('material'))) {
            $material = $this->input('material');
            array_shift($material);
            $this->merge([
                'material' => $material,
            ]);
        }
    }
    public function rules(): array
    {

        return [
            'warehouse_name' => 'required|gt:0',
            'quantity.*' => 'required',
            'material.*' => 'required',
            'title' => 'required',
            'warehouse_out' => 'required_if:is_processing_house,0',
            'processing_house' => 'required_if:is_processing_house,1',
            'expected_date' => 'required|date|after:yesterday',
            'status' => Rule::in(BaseStatusEnum::values()),
        ];
    }
    public function attributes(): array
    {
        return [
            'warehouse_name' => 'Tên kho',
            'quantity.*' => 'Số lượng (Kho)',
            'warehouse_out' => 'Kho xuất',
            'quantity_process.*' => 'Số lượng (Xử lý)',
            'material.*' => 'nguyên phụ liệu (Kho)',
            'material_process.*' => 'nguyên phụ liệu (Xử lý)',
            'expected_date' => 'Ngày dự kiến',
            'processing_house' => 'Nhà gia công',
            'status' => 'Trạng thái',
        ];
    }

    public function messages(): array
    {
        return [
            'warehouse_name.gt' => 'Phải chọn kho xuất',
            'warehouse_name.required' => 'Vui lòng chọn :attribute.',
            'quantity.*.required_if' => ':attribute là bắt buộc.',
            'warehouse_out.required_if' => 'Vui lòng chọn :attribute.',
            'quantity_process.*.required_if' => ':attribute là bắt buộc.',
            'material.*.required_if' => 'Vui lòng chọn :attribute.',
            'material_process.*.required_if' => 'Vui lòng chọn :attribute.',
            'expected_date.required' => 'Vui lòng nhập :attribute.',
            'expected_date.date' => ':attribute phải là một ngày hợp lệ.',
            'expected_date.after' => ':attribute phải sau hoặc bằng ngày hiện tại.',
            'processing_house.required_if' => 'Vui lòng chọn :attribute.',
            'status.in' => ':attribute không hợp lệ.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $exception = $validator->getException();

        throw (new $exception($validator))
            ->status(200)
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());
    }
}
