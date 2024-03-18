<?php

namespace Botble\Agent\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ProposalAgentReceiptRequest extends Request
{
    public function rules(): array
    {
        return [
            'status' => Rule::in(BaseStatusEnum::values()),
        ];
    }
}
