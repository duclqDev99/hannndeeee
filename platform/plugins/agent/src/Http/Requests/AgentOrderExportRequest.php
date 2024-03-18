<?php

namespace Botble\Agent\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AgentOrderExportRequest extends Request
{
    public function rules(): array
    {
        return [
            'agent_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ];
    }
}
