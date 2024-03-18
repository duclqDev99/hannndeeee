<?php

namespace Botble\Showroom\Listeners;

use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Facades\PaymentMethods;

class RemoveCodPaymentMethod
{
    public function handle(): void
    {
        PaymentMethods::method(PaymentMethodEnum::COD, [
            'html' => '',
            'priority' => 998,
        ]);
    }
}
