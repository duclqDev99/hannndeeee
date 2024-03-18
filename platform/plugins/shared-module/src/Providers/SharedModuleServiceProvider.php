<?php

namespace Botble\SharedModule\Providers;

use Botble\ACL\Models\User;
use Botble\Agent\Models\Agent;
use Botble\Agent\Models\AgentOrder;
use Botble\Agent\Models\AgentProduct;
use Botble\Agent\Models\AgentWarehouse;
use Botble\Base\Facades\Assets;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Supports\ServiceProvider;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\HubWarehouse\Models\DepartmentUser;
use Botble\HubWarehouse\Models\HubUser;
use Botble\HubWarehouse\Models\HubWarehouse;
use Botble\HubWarehouse\Models\QuantityProductInStock;
use Botble\HubWarehouse\Models\Warehouse;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;
use ArchiElite\NotificationPlus\Contracts\NotificationManager as NotificationManagerContract;
use Botble\Base\Facades\Html;
use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\PanelSections\PanelSectionItem;
use Botble\NotiAdminPusher\Models\AdminNotification;
use Botble\SaleWarehouse\Models\SaleUser;
use Botble\Setting\PanelSections\SettingOthersPanelSection;
use Botble\SharedModule\Drivers\MyTeleNoti;
use Botble\Showroom\Models\Showroom;
use Botble\Showroom\Models\ShowroomOrder;
use Botble\Showroom\Models\ShowroomWarehouse;
use Botble\Theme\Facades\Theme;
use Botble\Theme\Supports\ThemeSupport;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\File;

class SharedModuleServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/shared-module')
            ->loadHelpers(['helpers','constants','report-hub','report-showroom'])
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes();
        $this->app->booted(function () {
            $notificationManager = $this->app->make(NotificationManagerContract::class);

            $notificationManager->register(MyTeleNoti::class);
        });
        $this->app->register(CommandServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
        PanelSectionManager::default()->beforeRendering(function () {
            PanelSectionManager::registerItem(
                SettingOthersPanelSection::class,
                fn () => PanelSectionItem::make('telegram-chat-id')
                    ->setTitle('Telegram chat ID')
                    ->withIcon('ti ti-tag')
                    ->withDescription('Set chat ID for telegram channel')
                    ->withPriority(1)
                    ->withPermission('telegram.setting')
                    ->withRoute('telegram.setting.edit')
            );
        });





        Assets::addStylesDirectly([
            'vendor/core/plugins/shared-module/css/loading-admin.css',
            'vendor/core/plugins/shared-module/css/cms.css',
        ])->addScriptsDirectly('vendor/core/plugins/shared-module/js/loading-admin.js', 'header');
        Product::resolveRelationUsing('qrCode', function (Product $product) {
            return $product->hasMany(ProductQrcode::class, 'product_id');
        });
        User::resolveRelationUsing('warehouse_finished', function (User $user) {
            return $user->belongsToMany(WarehouseFinishedProducts::class, 'wfp_user_warehouse', 'user_id', 'warehouse_id');
        });
        User::resolveRelationUsing('department', function (User $user) {
            return $user->hasMany(DepartmentUser::class, 'user_id', 'id');
        });
        User::resolveRelationUsing('userHub', function (User $user) {
            return $user->hasMany(HubUser::class, 'user_id', 'id');
        });
        User::resolveRelationUsing('userSale', function (User $user) {
            return $user->hasMany(SaleUser::class, 'user_id', 'id');
        });
        User::resolveRelationUsing('hub', function (User $user) {
            return $user->belongsToMany(HubWarehouse::class, 'hb_user_hub', 'user_id', 'hub_id');
        });
        User::resolveRelationUsing('agent', function (User $user) {
            return $user->belongsToMany(Agent::class, 'agent_user', 'user_id', 'agent_id');
        });
        Agent::resolveRelationUsing('users', function (Agent $agent) {
            return $agent->belongsToMany(User::class, 'agent_user', 'agent_id', 'user_id');
        });

        User::resolveRelationUsing('notifications', function (User $user) {
            return $user->belongsToMany(AdminNotification::class, 'admin_notifications_seen')
                ->withPivot('viewed')
                ->withTimestamps();
        });
        User::resolveRelationUsing('showroom', function (User $user) {
            return $user->belongsToMany(Showroom::class, 'showroom_user', 'user_id', 'showroom_id');
        });
        Order::resolveRelationUsing('showroomOrder', function (Order $showroom) {
            return $showroom->hasOne(ShowroomOrder::class);
        });
        Agent::resolveRelationUsing('users', function (Agent $agent) {
            return $agent->belongsToMany(User::class, 'agent_user', 'agent_id', 'user_id');
        });
        Order::resolveRelationUsing('agentOrder', function (Order $agent) {
            return $agent->hasOne(AgentOrder::class);
        });

        Product::resolveRelationUsing('warehouseFinished', function (Product $product) {
            return $product->belongsToMany(WarehouseFinishedProducts::class, 'wfp_product_in_stock', 'product_id')
                ->withPivot('quantity');
        });
        Product::resolveRelationUsing('productAgent', function (Product $product) {
            return $product->belongsToMany(AgentWarehouse::class, 'agent_products', 'product_id', 'warehouse_id')->where('quantity_qrcode', '>', '0')
                ->withPivot('quantity_qrcode');
        });
        Product::resolveRelationUsing('productShowroom', function (Product $product) {
            return $product->belongsToMany(ShowroomWarehouse::class, 'showroom_products', 'product_id', 'warehouse_id')->where('quantity_qrcode', '>', '0')
                ->withPivot('quantity_qrcode');
        });
        Product::resolveRelationUsing('productBatches', function (Product $product) {
            return $product->hasMany(ProductBatch::class, 'product_parent_id');
        });

        Product::resolveRelationUsing('agentWarehouse', function (Product $product) {
            return $product->belongsTo(AgentWarehouse::class, 'id', 'agent_id');
        });
        Product::resolveRelationUsing('agentProduct', function (Product $product) {
            return $product->belongsTo(AgentProduct::class, 'id', 'product_id');
        });
        Product::resolveRelationUsing('productAttribute', function (Product $product) {
            return $product->hasMany(ProductVariation::class, 'product_id')
                ->join(
                    'ec_product_variation_items',
                    'ec_product_variation_items.variation_id',
                    '=',
                    'ec_product_variations.id'
                )
                ->join('ec_product_attributes', 'ec_product_attributes.id', '=', 'ec_product_variation_items.attribute_id')
                ->join(
                    'ec_product_attribute_sets',
                    'ec_product_attribute_sets.id',
                    '=',
                    'ec_product_attributes.attribute_set_id'
                )
                ->distinct()
                ->select([
                    'ec_product_variations.product_id',
                    'ec_product_variations.configurable_product_id',
                    'ec_product_attributes.title',
                    'ec_product_attributes.order',
                    'ec_product_attributes.attribute_set_id',
                    'ec_product_attribute_sets.title as attribute_set_title',
                    'ec_product_attribute_sets.slug as attribute_set_slug',
                ])
                ->orderBy('order');
        });



        add_filter(BASE_FILTER_BEFORE_RENDER_FORM, function ($form, $data) {
            if (get_class($form) == 'Botble\Ecommerce\Forms\ProductForm') {
                $form->addAfter('slug', 'ingredient', 'text', [
                    'label' => 'Thành phần',
                    'label_attr' => ['class' => 'control-label'],

                ]);
            }
            return $form;
        }, 120, 2);
        add_filter('ecommerce_checkout_header', function ($html) {
            $customScriptFile = 'vendor/core/plugins/shared-module/js/location.js';
            if (File::exists($customScriptFile)) {
                $html .= Html::script($customScriptFile, ['type' => 'text/javascript']);
            }
            return $html . ThemeSupport::getCustomJS('header');
        }, 15);

        add_filter('filter_product_homepage', function($data){
            if(array_key_exists('data', $data) ){
                if(array_key_exists('products', $data['data'])){
                    $products = $data['data']['products']->getCollection();
                    // dd($products);
                    $filteredProducts = $products->where('is_show_home', 1);
            
                    $perPage = $data['data']['products']->perPage();
                    $currentPage = Paginator::resolveCurrentPage();
                    $currentItems = $filteredProducts->slice(($currentPage - 1) * $perPage, $perPage)->all();
            
                    $data['data']['products'] = new LengthAwarePaginator($currentItems, count($filteredProducts), $perPage, $currentPage, ['path' => Paginator::resolveCurrentPath()]);
                }
            }
            return $data;
        },1,1);
    }
}
