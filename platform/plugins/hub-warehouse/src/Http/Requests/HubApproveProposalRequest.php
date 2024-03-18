<?php

namespace Botble\HubWarehouse\Http\Requests;

use Botble\Support\Http\Requests\Request;

class HubApproveProposalRequest extends Request
{
    public function rules(): array
    {

        return [
            'expected_date' => 'required|date|after:yesterday',
            'product' => 'required',
        ];
    }
    public function messages()
    {
        return [
            'expected_date.required' => 'Ngày dự kiến là bắt buộc',
            'expected_date.after' => 'Ngày dự kiến phải bằng hoặc sau ngày hiện tại',
        ];
    }
    public function attributes(){
        return [
            'product' => 'sản phẩm'
        ];
    }
}
