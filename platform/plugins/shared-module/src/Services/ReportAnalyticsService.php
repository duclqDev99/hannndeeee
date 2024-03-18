<?php
namespace Botble\SharedModule\Services;

use Botble\Analytics\Period;
use Botble\Analytics\Facades\Analytics;
use Botble\Dashboard\Supports\DashboardWidgetInstance;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class ReportAnalyticsService
{

    public function getGeneral($predefinedRange): array
    {
        $period = $this->getPeriodFromRequest($predefinedRange);

        $dimensions = $this->getDimensionFromRequest($predefinedRange);
        return $this->getTotalStats($period);

    }
    protected function getDimensionFromRequest($predefinedRange): string
    {
        $predefinedRangeFound = (new DashboardWidgetInstance())->getFilterRange($predefinedRange);
        return Arr::get([
            'this_week' => 'date',
            'last_7_days' => 'date',
            'this_month' => 'date',
            'last_30_days' => 'date',
            'this_year' => 'yearMonth',
        ], $predefinedRangeFound['key'], 'hour');
    }
    protected function getPeriodFromRequest($predefinedRange): Period
    {
        $dashboardInstance = new DashboardWidgetInstance();
        $predefinedRangeFound = $dashboardInstance->getFilterRange($predefinedRange);


        $startDate = $predefinedRangeFound['startDate'];
        $endDate = $predefinedRangeFound['endDate'];

        return Period::create($startDate, $endDate);
    }
    protected function getTotalStats(Period $period, string $dimensions = ''): array
    {
        if ($dimensions === 'hour') {
            $dimensions = 'date';
        }
        $sessions = 0;
        $totalUsers = 0;
        $activeUsers = 0;
        $screenPageViews = 0;
        $bounceRate = 0;

        $totalQuery = Analytics::performQuery($period, ['sessions','activeUsers', 'totalUsers', 'screenPageViews', 'bounceRate'], $dimensions)->toArray();
        foreach ($totalQuery as $item) {
            $sessions += $item['sessions'];
            $totalUsers += $item['totalUsers'];
            $activeUsers += $item['activeUsers'];
            $screenPageViews += $item['screenPageViews'];
            $bounceRate += Arr::get($item, 'bounceRate', 0);
        }
        return [$sessions, $activeUsers, $totalUsers, $screenPageViews, $bounceRate];
    }
    public function getPredefinedRangesDefault(): array
    {
        $endDate = Carbon::today()->endOfDay();

        return [
            [
                'key' => 'today',
                'label' => 'Hôm nay ' . Carbon::today()->format('d/m/Y') ,
                'startDate' => Carbon::today()->startOfDay(),
                'endDate' => $endDate,
            ],
            [
                'key' => 'yesterday',
                'label' => 'Hôm qua ' . Carbon::yesterday()->format('d/m/Y'),
                'startDate' => Carbon::yesterday()->startOfDay(),
                'endDate' => Carbon::yesterday()->endOfDay(),
            ],
            [
                'key' => 'this_week',
                'label' => 'Tuần này ' . Carbon::now()->startOfWeek()->format('d/m/Y') . ' - ' . Carbon::now()->endOfWeek()->format('d/m/Y'),
                'startDate' => Carbon::now()->startOfWeek(),
                'endDate' => Carbon::now()->endOfWeek(),
            ],
            [
                'key' => 'last_7_days',
                'label' => '7 ngày qua ' . Carbon::now()->subDays(7)->format('d/m/Y') . ' - ' . Carbon::now()->format('d/m/Y') ,
                'startDate' => Carbon::now()->subDays(7)->startOfDay(),
                'endDate' => $endDate,
            ],
            [
                'key' => 'this_month',
                'label' => 'Tháng này ' . Carbon::now()->startOfMonth()->format('d/m/Y') . ' - ' . Carbon::now()->endOfMonth()->format('d/m/Y'),
                'startDate' => Carbon::now()->startOfMonth(),
                'endDate' => $endDate,
            ],
            [
                'key' => 'last_30_days',
                'label' => '30 ngày qua ' . Carbon::now()->subDays(30)->format('d/m/Y') . ' - ' . Carbon::now()->format('d/m/Y'),
                'startDate' => Carbon::now()->subDays(29)->startOfDay(),
                'endDate' => $endDate,
            ],
            [
                'key' => 'this_year',
                'label' => 'Năm nay ' . Carbon::now()->startOfYear()->format('d/m/Y') . ' - ' . Carbon::now()->endOfYear()->format('d/m/Y'),
                'startDate' => Carbon::now()->startOfYear(),
                'endDate' => $endDate,
            ],
        ];
    }
}