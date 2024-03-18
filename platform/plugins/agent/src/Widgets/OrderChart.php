<?php

namespace Botble\Agent\Widgets;

use Botble\Agent\Models\Agent;
use Botble\Agent\Models\AgentOrder;
use Botble\Agent\Widgets\Traits\HasCategory;
use Botble\Base\Widgets\Chart;
use Botble\Ecommerce\Models\Order;

class OrderChart extends Chart
{
    use HasCategory;

    protected int $columns = 6;

    public function getLabel(): string
    {
        return trans('plugins/agent::reports.orders_chart');
    }

    public function getOptions(): array
    {
        if (isset(request()->agent_id)) {
            $agentId = (int)request()->agent_id;
        } else {
            $user = request()->user();

            $agentList = Agent::where('status', 'published')
                ->when(!$user->isSuperUser(), function ($query) use ($user) {
                    $query->whereIn('id', $user->agent->pluck('id'));
                })
                ->pluck('id')
                ->all();
            $agentId = reset($agentList);
        }

        $agenOrder = AgentOrder::query()
            ->where('where_id', $agentId)
            ->where('where_type', Agent::class)
            ->get()->pluck('order_id');

        $data = Order::query()
            ->selectRaw('count(id) as total, date_format(created_at, "' . $this->dateFormat . '") as period')
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->whereIn('id', $agenOrder)
            ->groupBy('period')
            ->pluck('total', 'period')
            ->all();

        return [
            'series' => [
                [
                    'name' => trans('plugins/agent::reports.number_of_orders'),
                    'data' => array_values($data),
                ],
            ],
            'xaxis' => [
                'categories' => $this->translateCategories($data),
            ],
        ];
    }
}
