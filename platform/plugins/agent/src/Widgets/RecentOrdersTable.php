<?php

namespace Botble\Agent\Widgets;

use Botble\Agent\Tables\Reports\RecentOrdersTable as BaseRecentOrdersTable;
use Botble\Base\Widgets\Table;

class RecentOrdersTable extends Table
{
    protected string $table = BaseRecentOrdersTable::class;

    protected string $route = 'agent.report.recent-orders';

    public function getLabel(): string
    {
        return trans('plugins/agent::reports.recent_orders');
    }
}
