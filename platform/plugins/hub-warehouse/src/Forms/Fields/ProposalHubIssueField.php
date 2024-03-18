<?php

namespace Botble\HubWarehouse\Forms\Fields;

use Arr;
use Botble\Agent\Enums\AgentStatusEnum;
use Botble\Agent\Models\Agent;
use Botble\Agent\Models\AgentWarehouse;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Forms\FormField;
use Botble\Base\Supports\Editor;
use Botble\HubWarehouse\Models\HubWarehouse;
use Botble\HubWarehouse\Models\Warehouse;
use Botble\InventoryDiscountPolicy\Models\InventoryDiscountPolicy;
use Botble\SaleWarehouse\Enums\SaleWarehouseStatusEnum;
use Botble\SaleWarehouse\Models\SaleWarehouse;
use Botble\Showroom\Enums\ShowroomStatusEnum;
use Botble\Showroom\Models\Showroom;
use Botble\Showroom\Models\ShowroomWarehouse;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;


class ProposalHubIssueField extends FormField
{
    protected function getTemplate(): string
    {

        return 'plugins/hub-warehouse::custom-fields.proposal-hub-issue-form';
    }

    public function render(array $options = [], $showLabel = true, $showField = true, $showError = true, $choices = []): string
    {
        $options['attr'] = Arr::set($options['attr'], 'class', Arr::get($options['attr'], 'class') . 'form-control');
        $agent = Agent::where('status',BaseStatusEnum::PUBLISHED)->get();
        $showroom = Showroom::where('status',BaseStatusEnum::PUBLISHED)->get();
        $hub = HubWarehouse::where('status',HubStatusEnum::ACTIVE)->get();
        $hubWarehouse = Warehouse::where('status',HubStatusEnum::ACTIVE)->get();
        $agentWarehouse = AgentWarehouse::where('status',AgentStatusEnum::ACTIVE)->get();
        $showroomWarehouse = ShowroomWarehouse::where('status',ShowroomStatusEnum::ACTIVE)->get();
        if(is_plugin_active('inventory-discount-policy'))
        {
            $policy = InventoryDiscountPolicy::where(['type_warehouse'=>SaleWarehouse::class])->get();
        }
        else{
            $policy = [];
        }
        $options['agent'] =  $agent;
        $options['hub'] =  $hub;
        $options['showroom'] =  $showroom;
        $options['agentWarehouse'] =  $agentWarehouse;
        $options['showroomWarehouse'] =  $showroomWarehouse;
        $options['hubWarehouse'] =  $hubWarehouse;
        $options['policy'] =  $policy;

        (new Editor())->registerAssets();
        return parent::render($options, $showLabel, $showField, $showError);
    }
}
