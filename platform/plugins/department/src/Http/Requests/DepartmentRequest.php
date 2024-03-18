<?php

namespace Botble\Department\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class DepartmentRequest extends Request
{

    
    public function rules(): array
    {
        $currRouteName = request()->route()->getName();
        $routesHas = ['department.edit.update', 'department.edit'];

        if (in_array($currRouteName, $routesHas)) {
            return [
                'name' => 'required|string|max:220',
                'status' => Rule::in(BaseStatusEnum::values()),
                'code' => ['required','string','max:50', Rule::unique('departments')->ignore($this->id)]
            ];
        }

        return [
            'name' => 'required|string|max:220',
            'status' => Rule::in(BaseStatusEnum::values()),
            'code' => 'required|string|max:50|unique:departments,code'
        ];
       
    }
}
