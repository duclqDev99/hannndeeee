<?php

namespace Botble\Agent\Repositories\Agents\Interfaces;

interface AgentRepositoryInterface
{
    public function getProductQrcodeInWarehouseByUser($agentId, $status):int;
}
