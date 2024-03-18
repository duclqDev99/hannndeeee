<?php

namespace Botble\Agent\Widgets;

use Botble\Agent\Models\Agent;
use Botble\Agent\Models\AgentStatistics;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class RevenueCard extends Card
{

    protected $agentId;

    public function __construct()
    {
        $agentList = getListAgentIdByUser();
        parent::__construct();
        $this->agentId = reset($agentList);
    }

    public function getOptions(): array
    {
        if (isset(request()->agent_id)) {
            $agentId = (int)request()->agent_id;
        } else {
            $agentId = $this->agentId;
        }
        $data = AgentStatistics::query()
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->where('where_id', $agentId)
            ->pluck('revenue')
            ->toArray();
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
        $startDate = clone $this->startDate;
        $endDate = clone $this->endDate;
        if (isset(request()->agent_id)) {
            $agentId = (int)request()->agent_id;
        } else {
            $agentId = $this->agentId;
        }
        // dd($agentId);

        $revenue = AgentStatistics::query()
            ->where('where_type', Agent::class)
            ->where('where_id', $agentId)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->sum('revenue');
        $currentPeriod = CarbonPeriod::create($startDate, $endDate);
        $previousPeriod = CarbonPeriod::create($startDate->subDays($currentPeriod->count()), $endDate->subDays($currentPeriod->count()));

        $currentRevenue = AgentStatistics::query()
            ->where('where_type', Agent::class)
            ->where('where_id', $agentId)
            ->whereDate('created_at', '>=', $currentPeriod->getStartDate())
            ->whereDate('created_at', '<=', $currentPeriod->getEndDate())
            ->sum('revenue');

        $previousRevenue = AgentStatistics::query()
            ->where('where_type', Agent::class)
            ->where('where_id', $agentId)
            ->whereDate('created_at', '>=', $previousPeriod->getStartDate())
            ->whereDate('created_at', '<=', $previousPeriod->getEndDate())
            ->sum('revenue');

        $result = $currentRevenue - $previousRevenue;

        $result > 0 ? $this->chartColor = '#4ade80' : $this->chartColor = '#ff5b5b';

        return array_merge(parent::getViewData(), [
            'content' => view(
                'plugins/agent::reports.widgets.revenue-card',
                compact('revenue', 'result')
            )->render(),
        ]);
    }
}
