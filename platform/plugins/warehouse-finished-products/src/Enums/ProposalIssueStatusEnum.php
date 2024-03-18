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
 * @method static BaseStatusEnum REFUSE()
 * @method static BaseStatusEnum EXAMINE()
 */
class ProposalIssueStatusEnum extends Enum
{
    public const APPOROVED = 'approved';
    public const DENIED = 'denied';
    public const EXAMINE = 'examine';

    public const PENDING = 'pending';
    public const CONFIRM = 'confirm';
    public const REFUSE = 'refuse';

    public static $langPath = 'plugins/warehouse-finished-products::enums.proposal-issue';

    public function toHtml(): string|HtmlString
    {

        return match ($this->value) {
            self::DENIED => Html::tag('span', self::DENIED()->label(), ['class' => 'badge bg-danger text-danger-fg'])
                ->toHtml(),
            self::PENDING => Html::tag('span', self::PENDING()->label(), ['class' => 'badge bg-primary text-primary-fg'])
                ->toHtml(),
            self::EXAMINE => Html::tag('span', self::EXAMINE()->label(), ['class' => 'badge bg-primary text-primary-fg'])
                ->toHtml(),
            self::APPOROVED => Html::tag('span', self::APPOROVED()->label(), ['class' => 'badge bg-success text-success-fg'])
                ->toHtml(),
            self::CONFIRM => Html::tag('span', self::CONFIRM()->label(), ['class' => 'badge bg-info text-info-fg'])
                ->toHtml(),
            self::REFUSE => Html::tag('span', self::REFUSE()->label(), ['class' => 'badge bg-warning text-warning-fg'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
    public function toValue(){
        return $this->value;
    }
}
