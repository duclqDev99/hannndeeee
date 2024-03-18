<?php

namespace Botble\Department\Enums;

use Botble\Base\Supports\Enum;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

/**
 * @method static OrderStatusEnum WAITING()
 * @method static OrderStatusEnum RECEIVED()
 * @method static OrderStatusEnum COMPLETED()
 * @method static OrderStatusEnum CANCELED()
 */
class DepartmentCodeEnum extends Enum
{
    public const SALE = 'sale';

    public const DESIGN = 'design';

    public const ADMIN = 'admin';

    public const ACCOUNTANT = 'accountant';

    public const KH = 'kh';

    public const HGF = 'hgf_admin';

    public const DRAFT = 'draft';



    public static $langPath = 'plugins/sales::orders.statuses.department';

    public function toHtml(): HtmlString|string
    {
        $color = match ($this->value) {
            self::SALE => 'warning',
            self::DESIGN => 'info',
            self::ADMIN => 'success',
            self::ACCOUNTANT => 'danger',
            self::KH => 'danger',
            self::HGF => 'danger',
            self::DRAFT => 'danger',
            default => null,
        };

        return Blade::render(sprintf('<x-core::badge label="%s" color="%s" />', $this->label(), $color));
    }
}
