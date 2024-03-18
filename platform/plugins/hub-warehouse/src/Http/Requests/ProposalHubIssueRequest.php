<?php

namespace Botble\HubWarehouse\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ProposalHubIssueRequest extends Request
{
    public function rules(): array
    {
        $rules = [
            "warehouse_issue_id" => 'required|gt:0',
            "hub_id" => 'required|gt:0',
            "title" => 'required',
            'listProduct.*.quantity' => 'numeric|min:1|lte:listProduct.*.quantityStock',
            'expected_date' => 'required|date|after:yesterday',
        ];
        $isWarehouse = $this->input('is_warehouse');
        if ($isWarehouse == '0') {
            $rules['warehouseAgent'] = 'required|numeric|gt:0';
            $rules['agent'] = 'required|numeric|gt:0';
        }
        if ($isWarehouse == '1') {
            $rules['hub'] = 'required|numeric|gt:0';
            $rules['warehouseHub'] = 'required|numeric|gt:0';
        }

        if ($isWarehouse == '2') {
            $rules['warehouse_out'] = 'required|numeric|gt:0';
        }

        if ($isWarehouse == '3') {
            $rules['warehouse_product'] = 'required|numeric|gt:0';
        }
        return $rules;
    }
    public function messages()
    {
        return [
            'listProduct.*.quantity.lte' => 'Số lượng đề xuất vượt quá số lượng trong kho, vui lòng nhập thêm.',
            'expected_date.required' => 'Ngày dự kiến là bắt buộc',
            'expected_date.after' => 'Ngày dự kiến phải bằng hoặc sau ngày hiện tại',
            'title.required' => "Mục đích xuất kho là bắt buộc"
        ];
    }
}
