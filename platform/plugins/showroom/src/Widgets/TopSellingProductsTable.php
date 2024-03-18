<?php

namespace Botble\Showroom\Widgets;

use Botble\Base\Widgets\Table;
use Botble\Showroom\Tables\Reports\TopSellingProductsTable as BaseTopSellingProductsTable;

class TopSellingProductsTable extends Table
{
    protected string $table = BaseTopSellingProductsTable::class;

    protected string $route = 'showroom.report.top-selling-products';

    protected int $columns = 6;

    public function getLabel(): string
    {
        return trans('plugins/showroom::reports.top_selling_products');
    }
}
