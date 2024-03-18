<?php

namespace Botble\Showroom\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ShowroomRequest extends Request
{
    public function rules(): array
    {
        $rules = [
            'hub_id' => 'required|integer|exists:hb_hubs,id',
            'name' => 'required|string|max:220',
            'status' => Rule::in(BaseStatusEnum::values()),
            'phone_number' => 'nullable|regex:/^0[0-9]{9,10}$/',
            'code' => 'required|unique:showrooms'
        ];

        if (!request()->isMethod('get')) {
            $rules['code'] = 'required|string|' . Rule::unique('showrooms', 'code')->ignore(request('id'));
        }

        return $rules;
    }
}
