<?php

namespace Botble\ProductQrcode\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static QRStatusEnum CREATED()
 * @method static QRStatusEnum INSTOCK()
 * @method static QRStatusEnum SOLD()
 * @method static QRStatusEnum PENDINGSTOCK()
 * @method static QRStatusEnum PENDINGSOLD()
 */
class QRStatusEnum extends Enum
{
    public const CREATED = 'created';
    public const INSTOCK = 'instock';
    public const SOLD = 'sold';
    public const PENDINGSOLD = 'pendingsold';
    public const PENDINGSTOCK = 'pendingstock';
    public const CANCELLED = 'cancelled';
    public const SHIPPING = 'shipping';
    public const PENDING = 'pending';
    public const INTOUR = 'in_tour';

    public static $langPath = 'plugins/product-qrcode::enum-status';

    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::CREATED => Html::tag('span', self::CREATED()->label(), ['class' => 'badge bg-secondary text-secondary-fg'])
                ->toHtml(),
            self::INSTOCK => Html::tag('span', self::INSTOCK()->label(), ['class' => 'badge bg-success text-success-fg'])
                ->toHtml(),
            self::SOLD => Html::tag('span', self::SOLD()->label(), ['class' => 'badge bg-warning text-warning-fg'])
                ->toHtml(),
            self::PENDINGSTOCK => Html::tag('span', self::PENDINGSTOCK()->label(), ['class' => 'badge bg-danger text-danger-fg'])
                ->toHtml(),
            self::PENDINGSOLD => Html::tag('span', self::PENDINGSOLD()->label(), ['class' => 'badge bg-danger text-danger-fg'])
                ->toHtml(),
            self::CANCELLED => Html::tag('span', self::CANCELLED()->label(), ['class' => 'badge bg-danger text-danger-fg'])
                ->toHtml(),
            self::INTOUR => Html::tag('span', self::INTOUR()->label(), ['class' => 'badge bg-warning text-warning-fg'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
}
