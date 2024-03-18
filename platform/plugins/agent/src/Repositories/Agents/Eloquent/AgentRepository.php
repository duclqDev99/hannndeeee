<?php

namespace Botble\Agent\Repositories\Agents\Eloquent;

use Botble\Agent\Models\AgentWarehouse;
use Botble\Agent\Repositories\Agents\Interfaces\AgentRepositoryInterface;
use Botble\Ecommerce\Models\Product;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;

class AgentRepository extends RepositoriesAbstract implements AgentRepositoryInterface
{
    public function getProductQrcodeInWarehouseByUser($agentId, $status): int
    {
        if (!isset($agentId)) {
            $agentList = getListAgentIdByUser();
            $agentId = reset($agentList);
        }

        $agentWarehouseByUser = AgentWarehouse::query()->where('agent_id', $agentId)->pluck('id')->toArray();
        return ProductQrcode::query()
            ->where('status', $status)
            ->where('warehouse_type', AgentWarehouse::class)
            ->where('reference_type', Product::class)
            ->whereIn('warehouse_id', $agentWarehouseByUser)
            ->count();
    }

}
