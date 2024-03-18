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
class MaterialProposalStatusEnum extends Enum
{
    public const APPOROVED = 'approved';
    public const DENIED = 'denied';
    public const PENDING = 'pending';
    public const CONFIRM = 'confirm';

    public static $langPath = 'plugins/warehouse::enums.proposal';

    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::DENIED => Html::tag('span', self::DENIED()->label(), ['class' => 'badge bg-danger text-danger-fg'])
                ->toHtml(),
            self::PENDING => Html::tag('span', self::PENDING()->label(),  ['class' => 'badge bg-primary text-primary-fg'])
                ->toHtml(),
            self::APPOROVED => Html::tag('span', self::APPOROVED()->label(), ['class' => 'badge bg-success text-success-fg'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
    public function toValue()
    {
        return $this->value;
    }
}
