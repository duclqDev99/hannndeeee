<?php

namespace Botble\Warehouse\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static BaseStatusEnum CAI()
 * @method static BaseStatusEnum CUON()
 * @method static BaseStatusEnum MET()
 * @method static BaseStatusEnum SET()
 */
class BaseUnitEnum extends Enum
{
    public const CAI = 'cai';
    public const CUON  = 'cuon';
    public const MET = 'met';

    public const SET = 'set';

    public static $langPath = 'plugins/warehouse::material.unit';

    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::CAI => Html::tag('span', self::CAI()->label(), ['class' => 'label-info status-label'])
                ->toHtml(),
            self::CUON => Html::tag('span', self::CUON()->label(), ['class' => 'label-warning status-label'])
                ->toHtml(),
            self::MET => Html::tag('span', self::MET()->label(), ['class' => 'label-success status-label'])
                ->toHtml(),
            self::SET => Html::tag('span', self::SET()->label(), ['class' => 'label-success status-label'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
    public function toValue(){
        return $this->value;
    }
}
