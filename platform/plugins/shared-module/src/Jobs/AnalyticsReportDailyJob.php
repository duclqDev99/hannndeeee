<?php

namespace Botble\SharedModule\Jobs;

use Botble\SharedModule\Services\ReportAnalyticsService;
use Botble\Showroom\Models\Showroom;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Telegram\Bot\Laravel\Facades\Telegram as TelegramBot;
use Spatie\Browsershot\Browsershot;
use Telegram\Bot\FileUpload\InputFile;

class AnalyticsReportDailyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $predefinedRange;
    protected $analyticsService;

    public function __construct($predefinedRange, ReportAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
        $this->predefinedRange = $predefinedRange;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if( setting('analytics_service_account_credentials')){
            [$sessions,$activeUsers, $totalUsers, $screenPageViews, $bounceRate] = $this->analyticsService->getGeneral($this->predefinedRange);
            $bounceRate = round($bounceRate * 100, 2)  . '%';
            $getPredefinedRangesDefault = $this->analyticsService->getPredefinedRangesDefault();
            
            $dateDisplayArr = array_filter($getPredefinedRangesDefault, function ($item) {
                return $item['key'] === $this->predefinedRange;
            });
            $dateDisplay = $dateDisplayArr[array_key_first($dateDisplayArr)]['label'];
            // Tạo bảng HTML
            $html = "
                    <style>
                        h1 {
                            text-align: center;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                        }
                        th, td {
                            border: 1px solid #ddd;
                            padding: 8px;
                            text-align: center;
                        }
                        th {
                            background-color: #4CAF50;
                            color: white;
                        }
                        tr:nth-child(even) {
                            background-color: #f2f2f2;
                        }
                    </style>
                    <h1>Truy cập $dateDisplay</h1>";
    
                    $html .= "<table>
                        <tr>
                            <th>Phiên</th>
                            <th>Số người truy cập</th>
                            <th>Số người tương tác</th>
                            <th>Lượt xem</th>
                            <th>Tỉ lệ thoát</th>
                        </tr>
                        <tr>
                            <td>$sessions</td>
                            <td>$totalUsers</td>
                            <td>$activeUsers</td>
                            <td>$screenPageViews</td>
                            <td>$bounceRate</td>
                        </tr>
                    </table>";
    
    
            Browsershot::html($html)
                ->windowSize(720, 250)
                ->fullPage()
                ->noSandbox()
                ->save(resource_path('reportAnalytics.png'));
    
    
            $result = TelegramBot::sendPhoto([
                'chat_id' => setting("tele_chat_id_report_daily"),
                'photo'   => InputFile::create(resource_path('reportAnalytics.png'), "báocáongày-$dateDisplay.png"),
            ]);
            if ($result->message_id) {
                echo 'Gửi báo cáo truy cập thành công!';
                \Log::info('Gửi báo cáo truy cập thành công!');
            } else {
                echo 'Gửi báo cáo Hub thất bại!';
                \Log::info('Gửi báo cáo truy cập thất bại!');
            }
        }
        else{
            echo 'Không có thông tin tài khoản dịch vụ phân tích!';
            \Log::info('Không có thông tin tài khoản dịch vụ phân tích!');
        }
        
    }

}
