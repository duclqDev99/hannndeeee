<?php

namespace Botble\WarehouseFinishedProducts\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static BaseStatusEnum APPOROVED()
 * @method static BaseStatusEnum PENDING()
 * @method static BaseStatusEnum CANCEL()
 */
class ApprovedStatusEnum extends Enum
{
    public const APPOROVED = 'approved';
    public const PENDING = 'pending';
    public const CANCEL = 'cancel';

    public static $langPath = 'plugins/warehouse::enums.statuses.receipt';

    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::PENDING => Html::tag('span', self::PENDING()->label(), ['class' => 'badge bg-warning text-warning-fg'])
                ->toHtml(),
            self::APPOROVED => Html::tag('span', self::APPOROVED()->label(), ['class' => 'badge bg-success text-success-fg'])
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
