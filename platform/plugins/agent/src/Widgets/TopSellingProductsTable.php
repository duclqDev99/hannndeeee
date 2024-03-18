<?php

namespace Botble\Agent\Widgets;

use Botble\Agent\Tables\Reports\TopSellingProductsTable as BaseTopSellingProductsTable;
use Botble\Base\Widgets\Table;

class TopSellingProductsTable extends Table
{
    protected string $table = BaseTopSellingProductsTable::class;

    protected string $route = 'agent.report.top-selling-products';

    protected int $columns = 6;

    public function getLabel(): string
    {
        return trans('plugins/agent::reports.top_selling_products');
    }
}
