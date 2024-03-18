<?php

namespace Botble\WarehouseFinishedProducts\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class WarehouseFinishedProductsRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:220|regex:/^[\p{L}\p{N}-]+(?:\s[\p{L}\p{N}-]+)*$/u',
            'status' => Rule::in(BaseStatusEnum::values()),
        ];
    }
}
