<?php

namespace Botble\Warehouse\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class MaterialProposalReceiptRequest extends Request {
    public function rules(): array {

        return [
            'expected_date' => 'required|date|after:yesterday',
        ];
    }
    public function attributes(): array {
        return [
            'expected_date' => 'Ngày dự kiến',
        ];
    }

    public function messages(): array {
        return [
            'expected_date.after' => ':attribute phải sau hoặc bằng ngày hiện tại.',
        ];
    }

    protected function failedValidation(Validator $validator) {
        $exception = $validator->getException();

        throw (new $exception($validator))
            ->status(200)
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());
    }
}
