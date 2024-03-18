<?php

namespace Botble\Department\Enums;

use Botble\Base\Supports\Enum;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

/**
 * @method static OrderStatusEnum WAITING()
 * @method static OrderStatusEnum RECEIVED()
 * @method static OrderStatusEnum COMPLETED()
 * @method static OrderStatusEnum CANCELED()
 */
class OrderDepartmentStatusEnum extends Enum
{
    public const WAITING = 'waiting';

    public const RECEIVED = 'received';

    public const COMPLETED = 'completed';

    public const CANCELED = 'canceled';

    public static $langPath = 'plugins/sales::orders.statuses.department';

    public function toHtml(): HtmlString|string
    {
        $color = match ($this->value) {
            self::WAITING => 'warning',
            self::RECEIVED => 'info',
            self::COMPLETED => 'success',
            self::CANCELED => 'danger',
            default => null,
        };

        return Blade::render(sprintf('<x-core::badge label="%s" color="%s" />', $this->label(), $color));
    }
}
