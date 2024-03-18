<?php

namespace Botble\Warehouse\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static BaseStatusEnum PENDING()
 * @method static BaseStatusEnum CONFIRM()

 */
class GoodsIssueEnum extends Enum
{
    public const PENDING = 'pending';
    public const CONFIRM = 'confirm';
    public static $langPath = 'plugins/warehouse::enums.good-issue';
    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::PENDING => Html::tag('span', self::PENDING()->label(), ['class' => 'badge bg-warning text-warning-fg'])
                ->toHtml(),
            self::CONFIRM => Html::tag('span', self::CONFIRM()->label(), ['class' => 'badge bg-success text-success-fg'])
                ->toHtml(),

            default => parent::toHtml(),
        };
    }
    public function toValue()
    {
        return $this->value;
    }
}
