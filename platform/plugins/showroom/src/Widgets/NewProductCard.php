<?php

namespace Botble\Showroom\Widgets;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Widgets\Card;
use Botble\Ecommerce\Models\Product;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\Showroom\Models\ShowroomWarehouse;

class NewProductCard extends Card
{
    public function getOptions(): array
    {
        $data = [];

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
        $showroomId = null;
        $listShowroom = get_showroom_for_user()->pluck('name', 'id');
        if (count($listShowroom) > 0) {
            $showroomId = $listShowroom->keys()->first();
        }
        if (isset(request()->showroom_id)) {
            $showroomId = (int)request()->showroom_id;
        }

        $showroom_warehouse_by_user = ShowroomWarehouse::query()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->where('showroom_id', $showroomId)
            ->pluck('id')
            ->toArray();

        $count = ProductQrcode::query()
            ->where('status', QRStatusEnum::INSTOCK)
            ->where('warehouse_type', ShowroomWarehouse::class)
            ->where('reference_type', Product::class)
            ->whereIn('warehouse_id', $showroom_warehouse_by_user)
            ->count();

//        if ($showroomId != null) {
//            $agenProduct = ShowroomProduct::query()
//                ->where('where_id', $showroomId)
//                ->where('where_type', Showroom::class)
//                ->whereDate('created_at', '>=', $this->startDate)
//                ->whereDate('created_at', '<=', $this->endDate)
//                ->get()->pluck('product_id');
//            $count = Product::query()
//                ->whereIn('id', $agenProduct)
//                ->where('is_variation', 1)
//                ->wherePublished()
//                ->count();
//        } else {
//            $count = 'Bạn không có quyền truy cập';
//        }

        // $startDate = clone $this->startDate;
        // $endDate = clone $this->endDate;

        // $currentPeriod = CarbonPeriod::create($startDate, $endDate);
        // $previousPeriod = CarbonPeriod::create($startDate->subDays($currentPeriod->count()), $endDate->subDays($currentPeriod->count()));

        // $currentProducts = Product::query()
        //     ->whereIn('id', $agenProduct)
        //     ->whereDate('created_at', '>=', $currentPeriod->getStartDate())
        //     ->whereDate('created_at', '<=', $currentPeriod->getEndDate())
        //     ->count();

        // $previousProducts = Product::query()
        //     ->whereIn('id', $agenProduct)
        //     ->whereDate('created_at', '>=', $previousPeriod->getStartDate())
        //     ->whereDate('created_at', '<=', $previousPeriod->getEndDate())
        //     ->count();

        // $result = $currentProducts - $previousProducts;

        $result = 0;
        $this->chartColor = '#4ade80';

        // $result > 0 ? $this->chartColor = '#4ade80' : $this->chartColor = '#ff5b5b';

        return array_merge(parent::getViewData(), [
            'content' => view(
                'plugins/showroom::reports.widgets.new-product-card',
                compact('count', 'result')
            )->render(),
        ]);
    }
}
