<?php

namespace Botble\Warehouse\Http\Requests;

use Botble\Support\Http\Requests\Request;
use Botble\Warehouse\Enums\StockStatusEnum;
use Illuminate\Validation\Rule;

class WarehouseMaterialRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:220',
            // 'phone_number' => 'regex:/^\d{10,11}$/|unique:wh_warehouse,phone_number,' . $this->id,
            // 'address' => 'min:1|max:255',
            'status' => Rule::in(StockStatusEnum::values()),
        ];
    }
}
