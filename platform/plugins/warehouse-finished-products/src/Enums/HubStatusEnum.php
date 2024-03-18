<?php

namespace Botble\WarehouseFinishedProducts\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static BaseStatusEnum INACTIVE()
 * @method static BaseStatusEnum ACTIVE()
 * @method static BaseStatusEnum PENDING()
 */
class HubStatusEnum extends Enum
{
    public const ACTIVE = 'active';
    public const INACTIVE = 'inactive';

    public static $langPath = 'plugins/warehouse::enums.statuses.status_stock';

    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::INACTIVE => Html::tag('span', self::INACTIVE()->label(), ['class' => 'badge bg-danger text-danger-fg'])
                ->toHtml(),
            self::ACTIVE => Html::tag('span', self::ACTIVE()->label(), ['class' => 'badge bg-success text-success-fg'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
    public function toValue(){
        return $this->value;
    }
}
