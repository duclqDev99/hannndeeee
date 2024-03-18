<?php

namespace Botble\Agent\Widgets;

use Botble\Agent\Models\Agent;
use Botble\Agent\Models\AgentStatistics;
use Botble\Base\Widgets\Html;
use Botble\Payment\Enums\PaymentStatusEnum;
use Carbon\CarbonPeriod;
use Illuminate\Support\Arr;

class ReportGeneralHtml extends Html
{
    public function getContent(): string
    {
        if (!is_plugin_active('payment')) {
            return '';
        }

        $count = [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ];

        $startDate = clone $this->startDate;
        $endDate = clone $this->endDate;
        if (isset(request()->agent_id)) {
            $agentId = (int)request()->agent_id;
        } else {
            $agentList = getListAgentIdByUser();
            $agentId = reset($agentList);
        }

        $revenues = AgentStatistics::query()
            ->where('where_type', Agent::class)
            ->where('where_id', $agentId)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->sum('revenue');

        $count['revenues'] = [
            [
                'label' => PaymentStatusEnum::COMPLETED()->label(),
                'value' => $revenues ? (int)$revenues : 0,
                'status' => true,
                'color' => '#80bc00',
            ],
        ];

        $revenues = AgentStatistics::query()
            ->where('where_type', Agent::class)
            ->where('where_id', $agentId)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)->get();
        $series = [];
        $dates = [];
        $earningSales = collect();
        $period = CarbonPeriod::create($this->startDate->startOfDay(), $this->endDate->endOfDay());

        $colors = ['#fcb800', '#80bc00'];

        $data = [
            'name' => get_application_currency()->title,
            'data' => [],
        ];

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $filtered = $revenues->filter(function ($item) use ($formattedDate) {
                return $item->created_at->format('Y-m-d') == $formattedDate;
            });

            $sum = $filtered->reduce(function ($carry, $item) {
                return $carry + $item->revenue;
            }, 0);

            $data['data'][] = (float)$sum;
        }

        $earningSales[] = [
            'text' => trans('plugins/agent::reports.items_earning_sales', [
                'value' => format_price(collect($data['data'])->sum()),
            ]),
            'color' => Arr::get($colors, $earningSales->count(), Arr::first($colors)),
        ];

        $series[] = $data;


        foreach ($period as $date) {
            $dates[] = $date->toDateString();
        }

        $colors = $earningSales->pluck('color');

        $salesReport = compact('dates', 'series', 'earningSales', 'colors');

        $revenues = fn(string $key): array => collect($count['revenues'])->pluck($key)->toArray();

        return view('plugins/agent::reports.widgets.revenues', compact('count', 'salesReport', 'revenues'))->render();
    }
}
