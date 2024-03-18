<?php

namespace Botble\OrderAnalysis\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class EditOrderAnalysisRequest extends Request
{
    public function rules(): array
    {
        $rules = [
            'descriptionForm' => 'required|max:255',
            'statusSubmit' => 'required|max:100',
            'expected_date' => 'required|date|after_or_equal:today',
        ];

        if ($this->input('statusSubmit') === 'approved') {
            $rules['analysis_id'] = 'required';
        }

        return $rules;
    }
}
