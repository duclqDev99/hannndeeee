<?php

namespace Botble\Warehouse\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class TypeMaterialRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|unique:wh_type_materials,name,'.$this->id.'|string|max:220',
            'status' => Rule::in(BaseStatusEnum::values()),
        ];
    }
}
