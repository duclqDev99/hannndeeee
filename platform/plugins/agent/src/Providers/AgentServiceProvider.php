<?php

namespace Botble\Agent\Providers;

use Botble\ACL\Models\User;
use Botble\Agent\Models\Agent;
use Botble\Agent\Models\AgentOrder;
use Botble\Agent\Models\AgentUser;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Supports\ServiceProvider;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\ProductQrcode\Models\ProductQrcode;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\URL;

class AgentServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/agent')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes(['web', 'report', 'order', 'product','customer']);
        $this->app->register(EventServiceProvider::class);

        $this->app['events']->listen(RouteMatched::class, function () {
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-agent',
                'priority' => 3,
                'parent_id' => null,
                'name' => 'Đại lý',
                'icon' => 'ti ti-home',
                'permissions' => ['agent.manager'],
            ])
                ->registerItem([
                    'id' => 'cms-plugins-agent-list-agent',
                    'priority' => 1,
                    'parent_id' => 'cms-plugins-agent',
                    'name' => 'Danh sách',
                    'icon' => 'ti ti-menu',
                    'url' => route('agent.index'),
                    'permissions' => ['agent.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-agent-list-warehouse',
                    'priority' => 2,
                    'parent_id' => 'cms-plugins-agent',
                    'name' => 'Kho hàng',
                    'icon' => 'ti ti-archive',
                    'url' => route('agent-warehouse.index'),
                    'permissions' => ['agent-warehouse.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-agent-list-product',
                    'priority' => 3,
                    'parent_id' => 'cms-plugins-agent',
                    'name' => 'Sản phẩm',
                    'icon' => 'ti ti-app-window-filled',
                    'url' => route('agent-product.index'),
                    'permissions' => ['agent-product.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-agent-report',
                    'priority' => 4,
                    'parent_id' => 'cms-plugins-agent',
                    'name' => 'Báo cáo',
                    'icon' => 'ti ti-chart-donut-2',
                    'url' => route('agent.report.index'),
                    'permissions' => ['agent.report.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-agent-order',
                    'priority' => 5,
                    'parent_id' => 'cms-plugins-agent',
                    'name' => 'Đơn hàng',
                    'icon' => 'ti ti-notebook',
                    'url' => route('agent.orders.index'),
                    'permissions' => ['agent.orders.index'],
                ])->
                registerItem([
                    'id' => 'cms-plugins-proposal-agent-receipt',
                    'priority' => 6,
                    'parent_id' => 'cms-plugins-agent',
                    'name' => 'Đề xuất nhập kho',
                    'icon' => 'ti ti-download',
                    'url' => route('proposal-agent-receipt.index'),
                    'permissions' => ['proposal-agent-receipt.index'],
                ])->
                registerItem([
                    'id' => 'cms-plugins-agent-receipt',
                    'priority' => 7,
                    'parent_id' => 'cms-plugins-agent',
                    'name' => 'Phiếu nhập kho',
                    'icon' => 'ti ti-clipboard-plus',
                    'url' => route('agent-receipt.index'),
                    'permissions' => ['agent-receipt.index'],
                ])->
                registerItem([
                    'id' => 'cms-plugins-agent-proposal-issue',
                    'priority' => 8,
                    'parent_id' => 'cms-plugins-agent',
                    'name' => 'Yêu cầu trả hàng',
                    'icon' => 'ti ti-upload',
                    'url' => route('agent-proposal-issue.index'),
                    'permissions' => ['agent-proposal-issue.index'],
                ])-> registerItem([
                    'id' => 'cms-plugins-agent-issue',
                    'priority' => 9,
                    'parent_id' => 'cms-plugins-agent',
                    'name' => 'Phiếu xuất kho',
                    'icon' => 'ti ti-clipboard',
                    'url' => route('agent-issue.index'),
                    'permissions' => ['agent-issue.index'],
                ]);
            ;
        });

        if (str_ends_with(request()->getHost(), '.ngrok-free.app')) {
            URL::forceScheme('https');
        }
    }
}
