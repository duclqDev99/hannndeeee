<?php

namespace Botble\SaleWarehouse\Http\Requests;

use Botble\Support\Http\Requests\Request;

class ApproveProposalRequest extends Request
{
    public function rules(): array
    {

        return [
            'expected_date' => 'required|date|after:yesterday',
            'product.*.quantity' => 'min:1|lte:product.*.quantityStock',
        ];
    }
    public function messages()
    {
        return [
            'product.*.quantity.lte' => 'Số lượng đề xuất vượt quá số lượng trong kho, vui lòng nhập thêm.',
            'expected_date.required' => 'Ngày dự kiến là bắt buộc',
            'expected_date.after' => 'Ngày dự kiến phải bằng hoặc sau ngày hiện tại',
        ];
    }
}
