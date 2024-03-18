<?php

namespace Botble\CustomerBookOrder\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class CustomerBookOrderRequest extends Request
{
    public function rules(): array
    {
        return [
            'username' => 'required|string|max:191',
            'email' => 'required|string|max:191',
            'phone' => 'required|string|max:40',
            'address' => 'nullable|string|max:191',
            'type_order' => 'required|string|max:60',
            'image.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,xbm,tif,pjp,apng|max:4096',
            'note' => 'nullable|string',
            'status' => Rule::in(BaseStatusEnum::values()),
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'image.*.required' => 'Vui lòng chọn một tập tin ảnh.',
            'image.*.image' => 'Tập tin phải là một hình ảnh.',
            'image.*.mimes' => 'Tập tin ảnh phải có định dạng là: jpeg, png, jpg, webp, xbm, apng hoặc gif.',
            'image.*.max' => 'Kích thước tập tin ảnh tối đa là 4MB.',
        ];
    }
}