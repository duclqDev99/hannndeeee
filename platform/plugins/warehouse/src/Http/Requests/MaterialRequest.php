<?php

namespace Botble\Warehouse\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Botble\Warehouse\Enums\MaterialStatusEnum;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class MaterialRequest extends Request
{
    public function rules(): array
    {
        return [
            'unit' => 'required',
            'name' => 'required|string|max:220',
            'code'=> 'required|unique:wh_materials,code,'.$this->id.'|min:5',
            // 'code'   => "required|min:5|unique:wh_materials,code,{$this->id}",
            'status' => Rule::in(MaterialStatusEnum::values()),
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
