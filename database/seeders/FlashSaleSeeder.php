<?php

namespace Database\Seeders;

use Botble\Base\Facades\MetaBox;
use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Models\FlashSale;
use Botble\Ecommerce\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class FlashSaleSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->uploadFiles('flash-sales');

        FlashSale::query()->truncate();

        $data = [
            [
                'name' => 'Deal of the Day.',
            ],
            [
                'name' => 'Gadgets & Accessories',
            ],
        ];

        $productIds = Product::query()->where('is_variation', 0)->pluck('id')->all();

        foreach ($data as $index => $item) {
            $item['end_date'] = Carbon::now()
                ->addDays(rand(15, 50))
                ->addHours(rand(1, 23))
                ->addMinutes(rand(1, 59))
                ->toDateString();

            $flashSale = FlashSale::query()->create($item);

            MetaBox::saveMetaBoxData($flashSale, 'image', 'flash-sales/' . ($index + 1) . '.jpg');

            if ($productIds) {
                $product = Product::query()->find(Arr::random($productIds));

                if ($product) {
                    $price = $product->price;

                    if ($product->front_sale_price !== $product->price) {
                        $price = $product->front_sale_price;
                    }

                    $flashSale->products()->attach([
                        $product->id => [
                            'price' => $price - ($price * rand(10, 70) / 100),
                            'quantity' => rand(6, 20),
                            'sold' => rand(1, 5),
                        ],
                    ]);
                }
            }
        }
    }
}
