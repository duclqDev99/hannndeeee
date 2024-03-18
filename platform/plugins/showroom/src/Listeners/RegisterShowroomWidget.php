<?php

namespace Botble\Showroom\Listeners;

use Botble\Base\Events\RenderingAdminWidgetEvent;
use Botble\Showroom\Widgets\BankTransferRevenueCard;
use Botble\Showroom\Widgets\CashRevenueCard;
use Botble\Showroom\Widgets\CustomerChart;
use Botble\Showroom\Widgets\NewCustomerCard;
use Botble\Showroom\Widgets\NewOrderCard;
use Botble\Showroom\Widgets\NewProductCard;
use Botble\Showroom\Widgets\OrderChart;
use Botble\Showroom\Widgets\RecentOrdersTable;
use Botble\Showroom\Widgets\ReportGeneralHtml;
use Botble\Showroom\Widgets\RevenueCard;
use Botble\Showroom\Widgets\TaxAmountCard;
use Botble\Showroom\Widgets\TopSellingProductsTable;
use Botble\Showroom\Widgets\TrendingProductsTable;

class RegisterShowroomWidget
{
    public function handle(RenderingAdminWidgetEvent $event): void
    {
        $event->widget
            ->register([
                RevenueCard::class,
                BankTransferRevenueCard::class,
                CashRevenueCard::class,
                TaxAmountCard::class,
                NewProductCard::class,
                NewCustomerCard::class,
                NewOrderCard::class,
                ReportGeneralHtml::class,
                CustomerChart::class,
                OrderChart::class,
                RecentOrdersTable::class,
                // TopSellingProductsTable::class,
                // TrendingProductsTable::class,
            ], 'showroom');
    }
}
