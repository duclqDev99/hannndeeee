<?php

namespace Botble\HubWarehouse\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;
use Illuminate\Validation\Rule;

class WarehouseRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:220',
            'status' => Rule::in(HubStatusEnum::values()),
            'hub_id' => 'required',
        ];
    }
}
