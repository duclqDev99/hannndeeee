<?php

namespace Botble\ProcedureOrder\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ProcedureOrderRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'parent_id' => 'required|string|max:100',
            'roles_join' => 'required|string',
            'next_step' => 'required|string',
            'main_thread_status' => ['required', 'string', 'max:255', Rule::in(['main_branch', 'secondary_branch'])],
        ];
    }
}
