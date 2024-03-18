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
class MaterialReceiptStatusEnum extends Enum
{
    public const APPOROVED = 'approved';
    public const DENIED = 'denied';
    public const PENDING = 'pending';

    public static $langPath = 'plugins/warehouse::enums.statuses.receipt';

    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::DENIED => Html::tag('span', self::DENIED()->label(), ['class' => 'badge bg-success text-success-fg'])
                ->toHtml(),
            self::PENDING => Html::tag('span', self::PENDING()->label(),  ['class' => 'badge bg-warning text-warning-fg'])
                ->toHtml(),
            self::APPOROVED => Html::tag('span', self::APPOROVED()->label(),  ['class' => 'badge bg-success text-success-fg'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
}
