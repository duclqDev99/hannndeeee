<?php

namespace Botble\OrderStepSetting\Enums;

use Botble\Base\Supports\Enum;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

/**
 * @method static OrderStatusEnum PROCESSING()
 * @method static OrderStatusEnum COMPLETED()
 * @method static OrderStatusEnum CANCEL()
 */
class ActionStatusEnum extends Enum
{

    public const NOT_READY = 'not_ready';

    public const PENDING = 'pending';

    public const PROCESSING = 'processing';

    public const SENDED = 'sended';

    public const CONFIRMED = 'confirmed';

    public const COMPLETED = 'completed';

    public const CANCELED = 'canceled';

    public const SIGNED = 'signed';

    public const DELIVERED = 'delivered';

    public const RECEIVED = 'received';

    public const  REFUSED = 'refused';

    public const CREATED = 'created';

    public static $langPath = 'plugins/order-step-setting::actions.statuses';

    public function toHtml(): HtmlString|string
    {
        $color = match ($this->value) {
            self::PROCESSING => 'primary',
            self::COMPLETED => 'success',
            self::CANCELED => 'danger',
            default => null,
        };

        return Blade::render(sprintf('<x-core::badge label="%s" color="%s" />', $this->label(), $color));
    }
}
