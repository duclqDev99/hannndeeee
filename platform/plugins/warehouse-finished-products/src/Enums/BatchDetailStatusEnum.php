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
 * @method static BaseStatusEnum CHANGE()
 */
class BatchDetailStatusEnum extends Enum
{
    public const INBATCH = 'inbatch';
    public const LOSING = 'losing';
    public const CHANGE = 'change';

    public const SOLD = 'sold';

    public static $langPath = 'plugins/warehouse-finished-products::enums.product-batch-detail';

    public function toHtml(): string|HtmlString
    {

        return match ($this->value) {
            self::INBATCH => Html::tag('span', self::PENDING()->label(), ['class' => 'badge bg-primary text-primary-fg'])
                ->toHtml(),
            self::LOSING => Html::tag('span', self::APPOROVED()->label(), ['class' => 'badge bg-success text-success-fg'])
                ->toHtml(),
            self::CHANGE => Html::tag('span', self::CHANGE()->label(), ['class' => 'badge bg-success text-success-fg'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
    public function toValue(){
        return $this->value;
    }
}
