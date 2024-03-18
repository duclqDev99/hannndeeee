<?php

namespace Botble\CustomLogin\Http\Rules;

use Botble\Ecommerce\Models\Customer;
use Illuminate\Contracts\Validation\Rule;
class CustomerPhoneMatch implements Rule
{
    public function passes($attribute, $value)
    {
        $countryCode = request('countrycode');
        if (strpos($value, $countryCode) === 0) {
            $phone = '+' . $value;
        } else {
            $phone = '+' . $countryCode . $value;
        }
        $phone = str_replace('+84', '0', $phone);
        return Customer::where('phone',$phone)->exists();
    }

    public function message()
    {
        return 'Số điện thoại chưa được đăng kí trên CMS.';
    }
}
