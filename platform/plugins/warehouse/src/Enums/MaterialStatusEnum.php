<?php

namespace Botble\Warehouse\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static BaseStatusEnum ACTIVE()
 * @method static BaseStatusEnum CANCEL()
 */
class MaterialStatusEnum extends Enum
{
    public const ACTIVE = 'active';
    public const CANCEL = 'cancel';

    public static $langPath = 'plugins/warehouse::enums.statuses.material';

    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::ACTIVE => Html::tag('span', self::ACTIVE()->label(), ['class' => 'badge bg-success text-success-fg'])
                ->toHtml(),
            self::CANCEL => Html::tag('span', self::CANCEL()->label(), ['class' => 'badge bg-danger text-danger-fg'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
    public function toValue(){
        return $this->value;
    }
}
