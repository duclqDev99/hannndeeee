<?php

namespace Botble\WarehouseFinishedProducts\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;
use Illuminate\Validation\Rule;

class ProposalProductIssueRequest extends Request
{


    public function rules(): array
    {
        $rules = [
            "warehouse_id" => 'required|gt:0',
            'expected_date' => 'required|date|after:yesterday',
            'title' => 'required',
            'status' => Rule::in(ProposalProductEnum::values()),
            'product.*.quantity' => 'required|numeric|min:1',
            'product' => 'required',
        ];

        if ($this->input('is_warehouse') == '0') {
            $rules['hub'] = 'required|gt:0';
            $rules['stock_warehouse'] = 'required|gt:0';
        }
        if ($this->input('is_warehouse') == '1') {
            $rules['warehouse_out'] = 'required|gt:0';
        }
        return $rules;
    }
    public function attributes(): array
    {
        return [
            'warehouse_id' => 'kho',
            'warehouse_out' => 'kho nhận',
            'expected_date' => 'ngày dự kiến',
            'title' => 'tiêu đề',
            'hub' => 'HUB',
            'product' => 'Sản phẩm',
            'product.*.quantity' => 'Số lượng sản phẩm',
            'stock_warehouse' => 'Kho nhận'
        ];
    }

    public function messages(): array
    {
        return [
            'gt' => 'Vui lòng chọn :attribute',
            'required' => 'Vui lòng nhâp :attribute.',
            'required_if' => 'Trường :attribute bắt buộc nhập',
            'date' => ':attribute phải là một ngày hợp lệ.',
            'after' => ':attribute phải sau hoặc bằng ngày hiện tại.',
        ];
    }
}
