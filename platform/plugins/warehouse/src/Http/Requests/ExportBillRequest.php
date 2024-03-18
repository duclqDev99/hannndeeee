<?php

namespace Botble\Warehouse\Http\Requests;

use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ExportBillRequest extends Request
{
    public function rules(): array
    {
        return [
            'proposal_name' => 'required|string|max:220',
            'receiver_name' => 'required|string|max:220',
            'storekeeper_name' =>  'required|string|max:220',
            'chief_accountant_name' =>  'required|string|max:220',
            'today' => 'required|date',
        ];
    }
}
