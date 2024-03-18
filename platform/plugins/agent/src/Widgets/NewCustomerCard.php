<?php

namespace Botble\Agent\Widgets;

use Botble\Agent\Models\Agent;
use Botble\Agent\Models\AgentCustomer;
use Botble\Base\Widgets\Card;
use Botble\Ecommerce\Models\Customer;
use Carbon\CarbonPeriod;

class NewCustomerCard extends Card
{
    public function getOptions(): array
    {
        $data = Customer::query()
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->selectRaw('count(id) as total, date_format(created_at, "' . $this->dateFormat . '") as period')
            ->groupBy('period')
            ->pluck('total')
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
        //đoạn code trên để lấy được agent_id(có 2 trường hợp là khi không có request thì sẽ lấy của usẻ còn khi có request thì sẽ lấy của request được chọn từ select)
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


        // $count = AgentCustomer::query()
        // ->where('where_id', $agentId)
        // ->where('where_type', Agent::class)
        // ->whereHas('customer', function ($query) use ($startDate, $endDate) {
        //     $query->whereDate('created_at', '>=', $startDate)
        //           ->whereDate('created_at', '<=', $endDate);
        // })
        // ->count();


        $agenCustomer = AgentCustomer::query()
            ->where('where_id', $agentId)
            ->where('where_type', Agent::class)
            ->get()->pluck('customer_id');
        $count = Customer::query()
            ->whereIn('id', $agenCustomer)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->count();


        $currentPeriod = CarbonPeriod::create($startDate, $endDate);
        $previousPeriod = CarbonPeriod::create($startDate->subDays($currentPeriod->count()), $endDate->subDays($currentPeriod->count()));

        $currentCustomers = Customer::query()
            ->whereIn('id', $agenCustomer)
            ->whereDate('created_at', '>=', $currentPeriod->getStartDate())
            ->whereDate('created_at', '<=', $currentPeriod->getEndDate())
            ->count();

        $previousCustomers = Customer::query()
            ->whereIn('id', $agenCustomer)
            ->whereDate('created_at', '>=', $previousPeriod->getStartDate())
            ->whereDate('created_at', '<=', $previousPeriod->getEndDate())
            ->count();

        $result = $currentCustomers - $previousCustomers;

        $result > 0 ? $this->chartColor = '#4ade80' : $this->chartColor = '#ff5b5b';

        return array_merge(parent::getViewData(), [
            'content' => view(
                'plugins/agent::reports.widgets.new-customer-card',
                compact('count', 'result')
            )->render(),
        ]);
    }
}
