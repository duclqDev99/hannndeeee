<?php

namespace Botble\Showroom\Widgets;

use Botble\Base\Widgets\Table;

class TrendingProductsTable extends Table
{
    protected string $table = \Botble\Showroom\Tables\Reports\TrendingProductsTable::class;

    protected string $route = 'showroom.report.trending-products';

    protected int $columns = 6;

    public function getLabel(): string
    {
        return trans('plugins/showroom::reports.trending_products');
    }
}
