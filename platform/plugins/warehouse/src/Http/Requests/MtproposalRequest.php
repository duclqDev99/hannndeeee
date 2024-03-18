<?php

namespace Botble\Warehouse\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class MtproposalRequest extends Request
{
    public function rules(): array
    {
        return [
            'inventory_id' => 'required',
            'quantity'=>'required',
            'supplier_id'=> 'required',
            'material' => 'required',
            'price_import'=> 'required'
        ];
    }
}
