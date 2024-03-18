<?php

namespace Botble\Agent\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AgentRequest extends Request
{
    public function rules(): array
    {
        $agentId = $this->route('agent')?->id;
        return [
            'name' => [
                'required',
                'string',
                'max:220',
                $agentId ? Rule::unique('agents')->ignore($agentId) : 'unique:agents,name',
            ],
            'discount_value' => 'nullable|integer|min:0',
            'discount_type' => 'nullable|in:VNĐ,%',
            'phone_number' => 'nullable|digits_between:9,13|starts_with:0',
            'status' => Rule::in(BaseStatusEnum::values()),
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator(Validator $validator)
    {
        $validator->sometimes('discount_value', 'max:100', function ($input) {
            return $input->discount_type == '%';
        });
    }
}
