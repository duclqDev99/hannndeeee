<?php

namespace Botble\Showroom\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ExchangeGoodsRequest extends Request
{
    public function rules(): array
    {
        $rules = [
            'showroom_id' => 'required|integer|exists:showroom_warehouse,id',
            'list_qrcode' => 'required',
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'showroom_id.required' => 'ID showroom là bắt buộc.',
            'showroom_id.integer' => 'ID showroom phải là một số.',
            'showroom_id.exists' => 'ID showroom không tồn tại.',
            'list_qrcode.required' => 'Phải tồn tại danh sách sản phẩm đổi/trả.',
        ];
    }
}
