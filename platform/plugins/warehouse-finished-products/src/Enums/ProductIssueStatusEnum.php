<?php

namespace Botble\WarehouseFinishedProducts\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static BaseStatusEnum APPOROVED()
 * @method static BaseStatusEnum PENDING()
 * @method static BaseStatusEnum DENIED()
 * @method static BaseStatusEnum PENDINGISSUE()
 */
class ProductIssueStatusEnum extends Enum
{
    public const APPOROVED = 'approved';
    public const PENDING = 'pending';
    public const PENDINGISSUE = 'pending_issue';
    public const DENIED = 'denied';

    public static $langPath = 'plugins/warehouse-finished-products::enums.product-issue';

    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::PENDING => Html::tag('span', self::PENDING()->label(), ['class' => 'badge bg-primary text-primary-fg'])
                ->toHtml(),
            self::APPOROVED => Html::tag('span', self::APPOROVED()->label(), ['class' => 'badge bg-success text-success-fg'])
                ->toHtml(),
            self::DENIED => Html::tag('span', self::DENIED()->label(), ['class' => 'badge bg-danger text-danger-fg'])
                ->toHtml(),
            self::PENDINGISSUE => Html::tag('span', self::PENDINGISSUE()->label(), ['class' => 'badge bg-danger text-warning-fg'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
    public function toValue(){
        return $this->value;
    }
}
