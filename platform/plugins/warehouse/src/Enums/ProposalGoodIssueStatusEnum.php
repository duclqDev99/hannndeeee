<?php

namespace Botble\Warehouse\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static BaseStatusEnum DENIED()
 * @method static BaseStatusEnum APPROVED()
 * @method static BaseStatusEnum PENDING()
 * @method static BaseStatusEnum CONFIRM()
 */
class ProposalGoodIssueStatusEnum extends Enum
{
    public const DENIED = 'denied';
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const CONFIRM = 'confirm';

    public static $langPath = 'plugins/warehouse::enums.proposal';

    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::DENIED => Html::tag('span', self::DENIED()->label(), ['class' => 'badge bg-danger text-danger-fg'])
                ->toHtml(),
            self::PENDING => Html::tag('span', self::PENDING()->label(), ['class' => 'badge bg-primary text-primary-fg'])
                ->toHtml(),
            self::APPROVED => Html::tag('span', self::APPROVED()->label(), ['class' => 'badge bg-success text-success-fg'])
                ->toHtml(),
            self::CONFIRM => Html::tag('span', self::CONFIRM()->label(), ['class' => 'badge bg-warning text-warning-fg'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
    public function toValue(){
        return $this->value;
    }
}
