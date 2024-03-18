<?php

namespace Botble\SharedModule\Jobs;

use Botble\HubWarehouse\Models\HubWarehouse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Telegram\Bot\Laravel\Facades\Telegram as TelegramBot;
use Spatie\Browsershot\Browsershot;
use Telegram\Bot\FileUpload\InputFile;

class HubReportDailyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $date;

    public function __construct($date)
    {
        $this->date = $date;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $date = $this->date;
        $listHub = HubWarehouse::select(['id', 'name'])->get();
        $dataSend = [];
        foreach ($listHub as $key => $hub) {
            $hubData = analysis_data_hub_by_date($hub->id, $date);
            if ($hubData) {
                $dataSend[$hub->name]['total_receipt'] = $hubData['countProductReceipt'];
                $dataSend[$hub->name]['total_issue'] = $hubData['countProductIssueInStatusApproved'];
                $dataSend[$hub->name]['product_instock'] = $hubData['countProductWarehouse'];
            }
        }
        if($date['startDate']->isSameDay($date['endDate']))
            $dateDisplay = 'ngày ' . $date['startDate']->format('d-m-Y');
        else{
            $dateDisplay = 'từ ' . $date['startDate']->format('d-m-Y') . ' đến ' . $date['endDate']->format('d-m-Y');

        }
        \Log::info(['hub'=>$dataSend]);

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
                <h1>Báo cáo HUB $dateDisplay </h1>";

        $html .= "<table>
                    <tr>
                        <th></th>";

                        foreach ($dataSend as $hub => $values) {
                            $html .= "<th>$hub</th>";
                        }

                        $html .= "</tr>
                    <tr>
                        <td>Tổng nhập</td>";

                        foreach ($dataSend as $values) {
                            $html .= "<td>{$values['total_receipt']}</td>";
                        }

                        $html .= "</tr>
                    <tr>
                        <td>Tổng xuất</td>";

                        foreach ($dataSend as $values) {
                            $html .= "<td>{$values['total_issue']}</td>";
                        }

                        $html .= "</tr>
                    <tr>
                        <td>Tồn kho</td>";

                        foreach ($dataSend as $values) {
                            $html .= "<td>{$values['product_instock']}</td>";
                        }

                        $html .= "</tr>
                </table>";


        Browsershot::html($html)
            ->windowSize(720, 250)
            ->fullPage()
            ->noSandbox()
            ->save(resource_path('reportHub.png'));


        $result = TelegramBot::sendPhoto([
            'chat_id' => setting("tele_chat_id_report_daily"),
            'photo'   => InputFile::create(resource_path('reportHub.png'), "báocáongày-$dateDisplay.png"),
        ]);
        if ($result->message_id) {
            echo 'Gửi báo cáo Hub thành công!';
            \Log::info('Gửi báo cáo Hub thành công!');
        } else {
            echo 'Gửi báo cáo hub thất bại!';
            \Log::error("Gửi báo cáo hub thất bại!");
        }
    }
}
