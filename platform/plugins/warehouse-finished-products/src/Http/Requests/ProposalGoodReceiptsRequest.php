<?php

namespace Botble\WarehouseFinishedProducts\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ProposalGoodReceiptsRequest extends Request
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:220',
            'warehouse_id' => 'required|int',
            'expected_date' => 'required',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'tiêu đề',
            'expected_date' => 'ngày dự kiến',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Giá trị :attribute là bắt buộc',
            'expected_date.required' => 'Giá trị :attribute là bắt buộc',
        ];
    }
}
