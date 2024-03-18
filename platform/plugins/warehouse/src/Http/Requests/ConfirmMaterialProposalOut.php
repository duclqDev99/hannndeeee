<?php

namespace Botble\Warehouse\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ConfirmMaterialProposalOut extends Request
{
    public function rules(): array
    {
        return [
            'expected_date' =>'required|date|after:yesterday',
        ];
    }
    public function messages(){
        return[
            'expected_date.required' => 'Ngày dự kiến là bắt buộc',
            'expected_date.after' => 'Ngày dự kiến phải sau ngày hiện tại',

        ];
    }
}
