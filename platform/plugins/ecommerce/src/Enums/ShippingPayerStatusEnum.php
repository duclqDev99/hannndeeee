<?php

namespace Botble\Ecommerce\Enums;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static ShippingStatusEnum NOT_APPROVED()
 * @method static ShippingStatusEnum APPROVED()
 * @method static ShippingStatusEnum ARRANGE_SHIPMENT()
 * @method static ShippingStatusEnum READY_TO_BE_SHIPPED_OUT()
 * @method static ShippingStatusEnum PICKING()
 * @method static ShippingStatusEnum PENDING()
 * @method static ShippingStatusEnum DELAY_PICKING()
 * @method static ShippingStatusEnum PICKED()
 * @method static ShippingStatusEnum NOT_PICKED()
 * @method static ShippingStatusEnum DELIVERING()
 * @method static ShippingStatusEnum DELIVERED()
 * @method static ShippingStatusEnum NOT_DELIVERED()
 * @method static ShippingStatusEnum AUDITED()
 * @method static ShippingStatusEnum CANCELED()
 */
class ShippingPayerStatusEnum extends Enum
{
    public const CUSTOMER = 'customer';

    public const SHOP = 'shop';

    public static $langPath = 'plugins/ecommerce::shipping.payer-statuses';

    // public function toHtml(): HtmlString|string
    // {
    //     $color = match ($this->value) {
    //         self::NOT_APPROVED, self::APPROVED, => 'info',
    //         default => 'primary',
    //     };

    //     return BaseHelper::renderBadge($this->label(), $color);
    // }
}
