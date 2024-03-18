<?php

namespace Botble\Agent\Widgets;

use Botble\Base\Widgets\Table;

class TrendingProductsTable extends Table
{
    protected string $table = \Botble\Agent\Tables\Reports\TrendingProductsTable::class;

    protected string $route = 'agent.report.trending-products';

    protected int $columns = 6;

    public function getLabel(): string
    {
        return trans('plugins/agent::reports.trending_products');
    }
}
