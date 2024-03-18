<?php

namespace Botble\Agent\Widgets;

use Botble\Agent\Models\Agent;
use Botble\Agent\Models\AgentCustomer;
use Botble\Agent\Widgets\Traits\HasCategory;
use Botble\Base\Widgets\Chart;
use Botble\Ecommerce\Models\Customer;

class CustomerChart extends Chart
{
    use HasCategory;

    protected int $columns = 6;

    public function getLabel(): string
    {
        return trans('plugins/agent::reports.customers_chart');
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
        $agenCustomer = AgentCustomer::query()
            ->where('where_id', $agentId)
            ->where('where_type', Agent::class)
            ->get()->pluck('customer_id');

        $data = Customer::query()
            ->groupBy('period')
            ->selectRaw('count(id) as total, date_format(created_at, "' . $this->dateFormat . '") as period')
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->whereIn('id', $agenCustomer)
            ->pluck('total', 'period')
            ->all();

        return [
            'series' => [
                [
                    'name' => trans('plugins/agent::reports.number_of_customers'),
                    'data' => array_values($data),
                ],
            ],
            'xaxis' => [
                'categories' => $this->translateCategories($data),
            ],
        ];
    }
}
