<?php

namespace Botble\CustomLogin\Http\Rules;

use Botble\ACL\Models\User;
use Illuminate\Contracts\Validation\Rule;
class UserPhoneMatch implements Rule
{
    public function passes($attribute, $value)
    {
        $countryCode = request('countrycode');
        if (strpos($value, $countryCode) === 0) {
            $phone = '+' . $value;
        } else {
            $phone = '+' . $countryCode . $value;
        }
        return User::where('phone', $phone)->exists();
    }

    public function message()
    {
        return 'Số điện thoại chưa được đăng kí trên CMS.';
    }
}
