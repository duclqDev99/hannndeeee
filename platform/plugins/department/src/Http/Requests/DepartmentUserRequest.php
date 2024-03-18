<?php

namespace Botble\Department\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class DepartmentUserRequest extends Request
{
    public function rules(): array
    {
        $currRouteName = request()->route()->getName();
        $routesHas = ['department-user.edit.update', 'department-user.edit'];
       
        if (in_array($currRouteName, $routesHas)) {
            return [
                'first_name' => 'required|max:60|min:2',
                'last_name' => 'required|max:60|min:2',
                'email' => ['required', 'max:60', 'min:6', 'email', Rule::unique('users')->ignore($this->id)],
                'phone' => 'required',
                'department_code' => 'required|exists:departments,code',
                // 'phone' => 'required|regex:/^\+84\d{9,11}$/|unique:users',
            ];
        }
        
        return [
            'first_name' => 'required|max:60|min:2',
            'last_name' => 'required|max:60|min:2',
            'email' => 'required|max:60|min:6|email|unique:users',
            'username' => 'required|alpha_dash|min:4|max:30||unique:users',
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'phone' => 'required',
            'department_code' => 'required|exists:departments,code',
            // 'phone' => 'required|regex:/^\+84\d{9,11}$/|unique:users',
        ];
    }
}
