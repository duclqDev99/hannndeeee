<?php

namespace Botble\SharedModule\Commands;

use Botble\SharedModule\Jobs\AnalyticsReportDailyJob;
use Illuminate\Console\Command;
use Botble\SharedModule\Jobs\HubReportDailyJob;
use Botble\SharedModule\Jobs\ShowroomReportDailyJob;
use Botble\SharedModule\Services\ReportAnalyticsService;
use Carbon\Carbon;

class ReportDailyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ReportDailyCommand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the ReportDailyCommand';

    /**
     * Execute the console command.
     *
     * @return mixeds
     */
    public function handle()
    {
        $date = [
            'startDate' => Carbon::yesterday()->startOfDay(),
            'endDate' => Carbon::yesterday()->endOfDay(),
        ];
        dispatch(new HubReportDailyJob($date));
        dispatch(new ShowroomReportDailyJob($date));
        dispatch(new AnalyticsReportDailyJob('yesterday', new ReportAnalyticsService()));
        if (Carbon::today()->isMonday()) {
            $date['startDate'] = Carbon::now()->subWeek()->startOfWeek();
            $date['endDate'] = Carbon::now()->subDay()->endOfWeek();
            dispatch(new HubReportDailyJob($date));
            dispatch(new ShowroomReportDailyJob($date));
            dispatch(new AnalyticsReportDailyJob('this_week', new ReportAnalyticsService()));
        }
    }
}
