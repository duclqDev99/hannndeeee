<?php

namespace Botble\Agent\Widgets;

use Botble\Agent\Repositories\Agents\Interfaces\AgentRepositoryInterface;
use Botble\ProductQrcode\Enums\QRStatusEnum;

class NewProductCard extends Card
{

    protected $agentRepository;

    public function __construct(AgentRepositoryInterface $AgentRepository)
    {
        parent::__construct();
        $this->agentRepository = $AgentRepository;
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
        $count = $this->agentRepository->getProductQrcodeInWarehouseByUser(request()->agent_id, QRStatusEnum::INSTOCK);

        $result = 0;
        return array_merge(parent::getViewData(), [
            'content' => view(
                'plugins/agent::reports.widgets.new-product-card',
                compact('count', 'result')
            )->render(),
        ]);
    }
}
