<?php

namespace Botble\SharedModule\Jobs;

use Botble\Showroom\Models\Showroom;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Telegram\Bot\Laravel\Facades\Telegram as TelegramBot;
use Spatie\Browsershot\Browsershot;
use Telegram\Bot\FileUpload\InputFile;
class ShowroomReportDailyJob implements ShouldQueue
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

        $listShowroom = Showroom::select(['id', 'name'])->get();
        $dataSend = [];
        foreach ($listShowroom as $key => $showroom) {
            $showroomData = analysis_data_showroom_by_date($showroom->id,$date);
            if ($showroomData) {

                $dataSend[$showroom->name]['revenue'] = number_format($showroomData['revenue'], 0, ',', '.');
                $dataSend[$showroom->name]['revenueBankTransfer'] = number_format($showroomData['revenueBankTransfer'], 0, ',', '.') ;
                $dataSend[$showroom->name]['revenueCash'] = number_format($showroomData['revenueCash'], 0, ',', '.') ;
                $dataSend[$showroom->name]['taxAmount'] = number_format($showroomData['taxAmount'], 0, ',', '.') ;
                $dataSend[$showroom->name]['product'] = $showroomData['product'];
                $dataSend[$showroom->name]['customer'] = $showroomData['customer'];
                $dataSend[$showroom->name]['order'] = $showroomData['order'];
                $dataSend[$showroom->name]['countProductIssue'] = $showroomData['countProductIssue'];
                $dataSend[$showroom->name]['countProductReceipt'] = $showroomData['countProductReceipt'];
                $dataSend[$showroom->name]['countProductSold'] = $showroomData['countProductSold'];
                $dataSend[$showroom->name]['countProductPendingSold'] = $showroomData['countProductPendingSold'];
                $dataSend[$showroom->name]['totalrFundedPointAmount'] = number_format($showroomData['totalrFundedPointAmount'], 0, ',', '.') ;
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
                <h1>Báo cáo Showroom $dateDisplay</h1>";

                $html .= "<table>
                <tr>
                    <th></th>";

            foreach ($dataSend as $hub => $values) {
                $html .= "<th>$hub</th>";
            }

            $html .= "</tr>
                <tr>
                    <td>Doanh thu</td>";

            foreach ($dataSend as $values) {
                $html .= "<td>{$values['revenue']}</td>";
            }

            $html .= "</tr>
                <tr>
                    <td>Doanh thu chuyển khoản</td>";

            foreach ($dataSend as $values) {
                $html .= "<td>{$values['revenueBankTransfer']}</td>";
            }

            $html .= "</tr>
                <tr>
                    <td>Doanh thu tiền mặt</td>";

            foreach ($dataSend as $values) {
                $html .= "<td>{$values['revenueCash']}</td>";
            }

            $html .= "</tr>
                <tr>
                    <td>Thuế</td>";

            foreach ($dataSend as $values) {
                $html .= "<td>{$values['taxAmount']}</td>";
            }

            $html .= "</tr>
                <tr>
                    <td>Khách hàng</td>";

            foreach ($dataSend as $values) {
                $html .= "<td>{$values['customer'][0]}</td>";
            }

            $html .= "</tr>
                <tr>
                    <td>Đơn hàng</td>";

            foreach ($dataSend as $values) {
                $html .= "<td>{$values['order'][0]}</td>";
            }

            $html .= "</tr>
                <tr>
                    <td>Số lượng sản phẩm nhập</td>";

            foreach ($dataSend as $values) {
                $html .= "<td>{$values['countProductReceipt']}</td>";
            }

            $html .= "</tr>
                <tr>
                    <td>Số lượng sản phẩm xuất</td>";

            foreach ($dataSend as $values) {
                $html .= "<td>{$values['countProductIssue']}</td>";
            }


            $html .= "</tr>
                <tr>
                    <td>Số lượng sản phẩm đã bán</td>";

            foreach ($dataSend as $values) {
                $html .= "<td>{$values['countProductSold']}</td>";
            }

            $html .= "</tr>
                <tr>
                    <td>Số lượng sản phẩm đang chờ bán</td>";

            foreach ($dataSend as $values) {
                $html .= "<td>{$values['countProductPendingSold']}</td>";
            }
            $html .= "</tr>
                <tr>
                    <td>Số lượng sản phẩm tồn</td>";

            foreach ($dataSend as $values) {
                $html .= "<td>{$values['product']}</td>";
            }
            $html .= "</tr>
                <tr>
                    <td>Tổng số điểm hoàn lại</td>";

            foreach ($dataSend as $values) {
                $html .= "<td>{$values['totalrFundedPointAmount']}</td>";
            }

            $html .= "</tr>
            </table>";


        Browsershot::html($html)
            ->windowSize(720, 250)
            ->fullPage()
            ->noSandbox()
            ->save(resource_path('reportShowroom.png'));


        $result = TelegramBot::sendPhoto([
            'chat_id' => setting("tele_chat_id_report_daily"),
            'photo'   => InputFile::create(resource_path('reportShowroom.png'), "báocáongày-$dateDisplay.png"),
        ]);
        if ($result->message_id) {
            echo 'Gửi báo cáo Showroom thành công!';
            \Log::info('Gửi báo cáo Showroom thành công!');
        } else {
            echo 'Gửi báo cáo Hub thất bại!';
            \Log::info('Gửi báo cáo Showroom thất bại!');
        }
    }
}
