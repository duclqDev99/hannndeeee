<?php

namespace Botble\WarehouseFinishedProducts\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static BaseStatusEnum DENIED()
 * @method static BaseStatusEnum APPOROVED()
 * @method static BaseStatusEnum PENDING()
 * @method static BaseStatusEnum CONFIRM()
 */
class ProductBatchStatusEnum extends Enum
{
    public const INSTOCK = 'instock';
    public const OUTSTOCK = 'outstock';
    public const ORTHER = 'orther';

    public static $langPath = 'plugins/warehouse-finished-products::enums.product-batch';

    public function toHtml(): string|HtmlString
    {

        return match ($this->value) {
            self::INSTOCK => Html::tag('span', self::PENDING()->label(), ['class' => 'badge bg-primary text-primary-fg'])
                ->toHtml(),
            self::OUTSTOCK => Html::tag('span', self::APPOROVED()->label(), ['class' => 'badge bg-success text-success-fg'])
                ->toHtml(),
            self::ORTHER => Html::tag('span', self::CONFIRM()->label(), ['class' => 'badge bg-warning text-warning-fg'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
    public function toValue(){
        return $this->value;
    }
}
