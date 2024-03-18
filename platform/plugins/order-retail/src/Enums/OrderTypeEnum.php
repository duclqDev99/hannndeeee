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
class OrderTypeEnum extends Enum
{
    public const SALE_PROMOTION = 'sale_promotion';
    public const SALE_CLUB = 'sale_club';
    public const SALE_TRANSFER = 'sale_transfer';
    public const SALE_FASHION = 'sale_fashion';

    public static $langPath = 'plugins/order-retail::order.type';

    public function toHtml(): HtmlString|string
    {
        $color = match ($this->value) {
            self::SALE_PROMOTION, self::SALE_CLUB, self::SALE_TRANSFER, self::SALE_FASHION => 'info',
            default => null,
        };

        return Blade::render(sprintf('<x-core::badge label="%s" color="%s" />', $this->label(), $color));
    }
}
