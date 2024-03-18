<?php

namespace Botble\Agent\Http\Requests;

use Botble\Agent\Enums\AgentStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class AgentWarehouseRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:220',
            'status' => Rule::in(AgentStatusEnum::values()),
        ];
    }
}
