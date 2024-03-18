<?php

namespace Botble\Showroom\Http\Requests;

use Botble\Showroom\Enums\ShowroomStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ShowroomWarehouseRequest extends Request
{
    public function rules(): array
    {
        return [
            'showroom_id' => 'required|integer',
            'name' => 'required|string|max:120',
            'address' => 'required|string|max:120',
            'description' => 'nullable|string',
            'status' => Rule::in(ShowroomStatusEnum::values()),
        ];
    }

    public function messages()
    {
        return [
            'showroom_id.required' => 'ID showroom là bắt buộc.',
            'showroom_id.integer' => 'ID showroom phải là một số.',
            'name.required' => 'Tên là bắt buộc.',
            'name.string' => 'Tên phải là một chuỗi ký tự.',
            'name.max' => 'Tên không được vượt quá 255 ký tự.',
            'address.required' => 'Địa chỉ là bắt buộc.',
            'address.string' => 'Địa chỉ phải là một chuỗi ký tự.',
            'address.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
            'description.string' => 'Mô tả phải là một chuỗi ký tự.',
        ];
    }
}
