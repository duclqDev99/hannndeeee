<?php

namespace Botble\Showroom\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static BaseStatusEnum INACTIVE()
 * @method static BaseStatusEnum ACTIVE()
 * @method static BaseStatusEnum PENDING()
 */
class ExchangeGoodsStatusEnum extends Enum
{
    public const WAITING = 'waiting';
    public const APPROVED = 'approved';
    public const CANCELED = 'canceled';

    public static $langPath = 'plugins/showroom::showroom.exchange_goods.statuses';

    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::CANCELED => Html::tag('span', self::CANCELED()->label(), ['class' => 'badge bg-danger text-danger-fg'])
                ->toHtml(),
            self::APPROVED => Html::tag('span', self::APPROVED()->label(), ['class' => 'badge bg-success text-success-fg'])
                ->toHtml(),
            self::WAITING => Html::tag('span', self::WAITING()->label(), ['class' => 'badge bg-info text-warning-fg'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
    public function toValue(){
        return $this->value;
    }
}
