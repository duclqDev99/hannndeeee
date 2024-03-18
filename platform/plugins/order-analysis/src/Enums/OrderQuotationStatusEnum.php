<?php

namespace Botble\OrderAnalysis\Enums;

use Botble\Base\Supports\Enum;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

/**
 * @method static OrderStatusEnum PENDING()
 * @method static OrderStatusEnum PROCESSING()
 * @method static OrderStatusEnum COMPLETED()
 * @method static OrderStatusEnum CANCELED()
 */
class OrderQuotationStatusEnum extends Enum
{
    public const APPROVED = 'approved';

    public const CANCELED = 'canceled';

    public const CREATED = 'created';

    public static $langPath = 'plugins/order-analysis::enums.order-analysis';

    public function toHtml(): HtmlString|string
    {
        $color = match ($this->value) {
            self::APPROVED => 'success',
            self::CANCELED => 'danger',
            self::CREATED => 'warning',
            default => null,
        };

        return Blade::render(sprintf('<x-core::badge label="%s" color="%s" />', $this->label(), $color));
    }
}