<?php

namespace Botble\CustomLogin\Http\Requests;

use Botble\CustomLogin\Http\Rules\CustomerPhoneMatch;
use Botble\CustomLogin\Rules\ReCaptcha;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerOtpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'phonenumber' => [
                'bail',
                'numeric',
                'regex:/^\+?([0-9\s\-\(\)])*$/',
                'digits_between:9,11',
                new CustomerPhoneMatch,
            ],
            'countrycode' => 'bail|numeric',
            'g-recaptcha-response' => ['required', new ReCaptcha],
        ];
    }
    public function attributes()
    {
        return [
            'phonenumber' => 'Số điện thoại',
            'countrycode' => 'Mã vùng điện thoại quốc tế',
            'g-recaptcha-response' => 'Mã Captcha',
        ];
    }

    public function messages()
    {
        return [
            'required' => ':attribute không được rỗng.',
            'numeric' => ':attribute phải là số.',
            'digits' => ':attribute phải chứa :digits ký tự số.',
            'digits_between' => ':attribute phải có từ :min đến :max chữ số.',
            // 'min' => ':attribute phải chứa ít nhất :min ký tự số.',
            // 'max' => ':attribute chứa tối đa :max ký tự số.',
            'g-recaptcha-response.required' => 'Vui lòng xác nhận không phải là người máy!'
        ];
    }

}
