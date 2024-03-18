<?php

namespace Botble\InventoryDiscountPolicy\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class InventoryDiscountPolicyRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:220',
            'code' => 'required|string|max:220',
            'type_warehouse' => 'required',
            'start_date' => 'required',
            'end_date' => [
                Rule::requiredIf(!$this->input('unlimited_time')),
                function ($attribute, $value, $fail) {
                    if (!empty ($value)) {
                        $endDate = Carbon::parse($value);
                        $startDate = Carbon::parse($this->input('start_date'));
                        $oneWeekLater = $startDate->copy()->addWeek();

                        if ($endDate->lte($oneWeekLater)) {
                            $fail('Ngày kết thúc phải sau ngày bắt đầu ít nhất 1 tuần.');
                        }
                    }
                },
            ],
            'value' =>
            ['required', 'string', 'numeric', 'min:0'],
            'status' => Rule::in(HubStatusEnum::values()),
            'document' => 'mimes:doc,docx,pdf'
        ];
    }
}
