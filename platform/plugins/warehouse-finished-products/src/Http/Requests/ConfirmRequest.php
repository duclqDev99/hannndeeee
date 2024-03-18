<?php

namespace Botble\WarehouseFinishedProducts\Http\Requests;

use Botble\Support\Http\Requests\Request;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;
use Illuminate\Validation\Rule;

class ConfirmRequest extends Request
{
    public function rules(): array
    {
        return [

        ];
    }
}
