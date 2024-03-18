<?php

namespace Botble\Sales\Enums;

use Botble\Base\Supports\Enum;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

/**
 * @method static OrderStatusEnum PENDING()
 * @method static OrderStatusEnum PROCESSING()
 * @method static OrderStatusEnum COMPLETED()
 * @method static OrderStatusEnum CANCELED()
 */
class TypeOrderEnum extends Enum
{
    public const SAMPLE = 'order_sample';

    public const OFFICIAL = 'order_official';

    public const ADDITIONAL = 'order_additional';

    public static $langPath = 'plugins/sales::orders';

    public function toHtml(): HtmlString|string
    {
        $color = match ($this->value) {
            self::SAMPLE => 'secondary',
            self::OFFICIAL => 'info',
            self::ADDITIONAL => 'success',
            default => null,
        };

        return Blade::render(sprintf('<x-core::badge label="%s" color="%s" />', $this->label(), $color));
    }
}
