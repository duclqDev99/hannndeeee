<?php

namespace Botble\HubWarehouse\Http\Requests;

use Botble\Support\Http\Requests\Request;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;
use Illuminate\Validation\Rule;

class HubWarehouseRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:220',
            'status' => Rule::in(HubStatusEnum::values()),
            'phone_number' => 'nullable|digits_between:9,13|starts_with:0',

        ];
    }
}
