<?php

namespace Botble\WarehouseFinishedProducts\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static BaseStatusEnum DRAFT()
 * @method static BaseStatusEnum PUBLISHED()
 */
class WarehouseEnum extends Enum
{
    public const PUBLISHED = 'published';
    public const DRAFT = 'draft';

    public static $langPath = 'plugins/warehouse::enums.statuses.warehouse';

    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::DRAFT => Html::tag('span', self::DRAFT()->label(), ['class' => 'badge bg-secondary text-secondary-fg']),
            self::PUBLISHED => Html::tag('span', self::PUBLISHED()->label(), ['class' => 'badge bg-success text-success-fg']),
            default => parent::toHtml(),
        };
    }
    public function toValue(){
        return $this->value;
    }
}
