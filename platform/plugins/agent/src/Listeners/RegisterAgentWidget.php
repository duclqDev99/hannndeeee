<?php

namespace Botble\Agent\Listeners;

use Botble\Base\Events\RenderingAdminWidgetEvent;
use Botble\Agent\Widgets\CustomerChart;
use Botble\Agent\Widgets\NewCustomerCard;
use Botble\Agent\Widgets\NewOrderCard;
use Botble\Agent\Widgets\NewProductSoldCard;
use Botble\Agent\Widgets\NewProductCard;
use Botble\Agent\Widgets\OrderChart;
use Botble\Agent\Widgets\RecentOrdersTable;
use Botble\Agent\Widgets\ReportGeneralHtml;
use Botble\Agent\Widgets\RevenueCard;
use Botble\Agent\Widgets\TopSellingProductsTable;
use Botble\Agent\Widgets\TrendingProductsTable;

class RegisterAgentWidget
{
    public function handle(RenderingAdminWidgetEvent $event): void
    {
        $event->widget
            ->register([
                RevenueCard::class,
                NewProductCard::class,
                NewProductSoldCard::class,
//                NewCustomerCard::class,
//                NewOrderCard::class,
                // CustomerChart::class,
                // OrderChart::class,
                ReportGeneralHtml::class,
                // RecentOrdersTable::class,
                TopSellingProductsTable::class,
                TrendingProductsTable::class,
            ], 'agent');
    }
}
