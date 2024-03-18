<?php

namespace Botble\CustomerBookOrder\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static BaseStatusEnum INACTIVE()
 * @method static BaseStatusEnum ACTIVE()
 * @method static BaseStatusEnum PENDING()
 */
class OrderStatusEnum extends Enum
{
    public const UNIFORM = 'uniform';
    public const CLUB = 'uniform-club';
    public const CUSTOM = 'custom';

    public static $langPath = 'plugins/customer-book-order::enums.statuses';

    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::UNIFORM => Html::tag('span', self::UNIFORM()->label(), ['class' => 'badge bg-danger text-danger-fg'])
                ->toHtml(),
            self::CLUB => Html::tag('span', self::CLUB()->label(), ['class' => 'badge bg-success text-success-fg'])
                ->toHtml(),
            self::CUSTOM => Html::tag('span', self::CUSTOM()->label(), ['class' => 'badge bg-info text-info-fg'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
    public function toValue(){
        return $this->value;
    }
}
