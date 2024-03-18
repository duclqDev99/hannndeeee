<?php

namespace Botble\Showroom\Rules;

use Botble\Ecommerce\Models\Customer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckUniqueEmailWithVID implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $emailExists =
            Customer::query()
            ->whereNot('phone', request('address.phone'))
            ->where('email',  request('address.email'))
            ->exists();

        if($emailExists) $fail('Email đã được sử dụng, vui lòng nhập email khác!');
    }
}
