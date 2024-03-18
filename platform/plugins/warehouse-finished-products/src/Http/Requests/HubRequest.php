<?php

namespace Botble\WarehouseFinishedProducts\Http\Requests;

use Botble\Support\Http\Requests\Request;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;
use Illuminate\Validation\Rule;

class HubRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:220',
            'status' => Rule::in(HubStatusEnum::values()),
        ];
    }
}
