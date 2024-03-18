<?php

namespace Botble\Warehouse\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static BaseStatusEnum DENIED()
 * @method static BaseStatusEnum APPOROVED()
 * @method static BaseStatusEnum PENDING()
 */
class PurchaseOrderStatusEnum extends Enum
{
    public const APPOROVED = 'approved';
    public const PENDING = 'pending';
    public const DENIED = 'denied';

    public static $langPath = 'plugins/warehouse::enums.statuses.purchase-order';

    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::PENDING => Html::tag('span', self::PENDING()->label(), ['class' => 'badge bg-success text-success-fg'])
                ->toHtml(),
            self::APPOROVED => Html::tag('span', self::APPOROVED()->label(),  ['class' => 'badge bg-success text-success-fg'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
    public function toValue(){
        return $this->value;
    }
}
