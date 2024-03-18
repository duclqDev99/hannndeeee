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
class ProductShippingTypeEnum extends Enum
{
    public const HUB = 'hub';
    public const DELIVERY = 'delivery';

    public static $langPath = 'plugins/order-retail::order.shipping_type';

    public function toHtml(): HtmlString|string
    {
        $color = match ($this->value) {
            self::HUB, self::DELIVERY => 'info',
            
            default => null,
        };

        return Blade::render(sprintf('<x-core::badge label="%s" color="%s" />', $this->label(), $color));
    }
}
