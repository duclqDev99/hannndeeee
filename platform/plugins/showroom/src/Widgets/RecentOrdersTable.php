<?php

namespace Botble\Showroom\Widgets;

use Botble\Base\Widgets\Table;
use Botble\Showroom\Tables\Reports\RecentOrdersTable as BaseRecentOrdersTable;

class RecentOrdersTable extends Table
{
    protected string $table = BaseRecentOrdersTable::class;

    protected string $route = 'showroom.report.recent-orders';

    public function getLabel(): string
    {
        return trans('plugins/showroom::reports.recent_orders');
    }
}
