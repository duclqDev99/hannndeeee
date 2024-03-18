<?php

namespace Botble\Showroom\Widgets;

use Botble\Base\Widgets\Chart;
use Botble\Ecommerce\Models\Customer;
use Botble\Showroom\Models\Showroom;
use Botble\Showroom\Models\ShowroomCustomer;
use Botble\Showroom\Widgets\Traits\HasCategory;

class CustomerChart extends Chart
{
    use HasCategory;

    protected int $columns = 6;

    public function getLabel(): string
    {
        return trans('plugins/showroom::reports.customers_chart');
    }

    public function getOptions(): array
    {
        $showroomId = null;
        $listShowroom = get_showroom_for_user()->pluck('name', 'id');
        if (count($listShowroom) > 0) {
            $showroomId = $listShowroom->keys()->first();
        }

        if (!empty(request()->showroom_id)) {
            $showroomId = (int)request()->showroom_id;
        }
        if ($showroomId != null) {
            $customer = ShowroomCustomer::query()
                ->where('where_id', $showroomId)
                ->where('where_type', Showroom::class)
                ->get()->pluck('customer_id');
            $data = Customer::query()
                ->groupBy('period')
                ->selectRaw('count(id) as total, date_format(created_at, "' . $this->dateFormat . '") as period')
                ->whereDate('created_at', '>=', $this->startDate)
                ->whereDate('created_at', '<=', $this->endDate)
                ->whereIn('id', $customer)
                ->pluck('total', 'period')
                ->all();
        } else {
            $data = ['Bạn không có quyền truy cập'];
        }

        return [
            'series' => [
                [
                    'name' => trans('plugins/showroom::reports.number_of_customers'),
                    'data' => array_values($data),
                ],
            ],
            'xaxis' => [
                'categories' => $this->translateCategories($data),
            ],
        ];
    }
}
