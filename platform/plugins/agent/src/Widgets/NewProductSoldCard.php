<?php

namespace Botble\Agent\Widgets;

use Botble\Agent\Repositories\Agents\Interfaces\AgentRepositoryInterface;
use Botble\ProductQrcode\Enums\QRStatusEnum;

class NewProductSoldCard extends Card
{
    protected $agentRepositories;

    public function __construct(AgentRepositoryInterface $agentRepositories)
    {
        $this->agentRepositories = $agentRepositories;
        parent::__construct();

    }

    public function getOptions(): array
    {
        $data = [];
        return [
            'series' => [
                [
                    'data' => $data,
                ],
            ],
        ];
    }

    public function getViewData(): array
    {
        if (isset(request()->agent_id)) {
            $agentId = (int)request()->agent_id;
        } else {
            $agentList = getListAgentIdByUser();
            $agentId = reset($agentList);
        }

        $count = $this->agentRepositories->getProductQrcodeInWarehouseByUser($agentId, QRStatusEnum::SOLD);

        $result = 0;
        return array_merge(parent::getViewData(), [
            'content' => view(
                'plugins/agent::reports.widgets.new-product-sold-card',
                compact('count', 'result')
            )->render(),
        ]);
    }
}
