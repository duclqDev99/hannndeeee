<?php

namespace Botble\Payment\Enums;

use Botble\Base\Supports\Enum;

/**
 * @method static PaymentMethodEnum COD()
 * @method static PaymentMethodEnum BANK_TRANSFER()
 */
class ExtendedPaymentMethodEnum extends PaymentMethodEnum
{
    
    public const CASH = 'cash';

}
