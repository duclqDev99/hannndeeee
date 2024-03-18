<?php

namespace Botble\OrderRetail\Enums;

use Botble\Base\Supports\Enum;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

/**
 * @method static OrderStatusEnum PENDING()
 * @method static OrderStatusEnum PROCESSING()
 * @method static OrderStatusEnum COMPLETED()
 * @method static OrderStatusEnum CANCELED()
 */
class ShippingMethodEnum extends Enum
{
    public const GHTK = 'ghtk';
    public const VIETTEL_POST = 'viettel_post';
    public const GHN = 'ghn';

    public static $langPath = 'plugins/order-retail::order.shipping_method';

    public function toHtml(): HtmlString|string
    {
        $color = match ($this->value) {
            self::GHTK, self::VIETTEL_POST, self::GHN, => 'info',
            default => null,
        };

        return Blade::render(sprintf('<x-core::badge label="%s" color="%s" />', $this->label(), $color));
    }
}
