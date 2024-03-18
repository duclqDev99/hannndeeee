<?php

namespace Botble\SaleWarehouse\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\SaleWarehouse\Enums\SaleWarehouseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class SaleWarehouseChildRequest extends Request
{
    public function rules(): array
    {
        return [
            'status' => Rule::in(SaleWarehouseStatusEnum::values()),
        ];
    }
}
