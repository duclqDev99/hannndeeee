<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Models\ProductCollection;
use Illuminate\Support\Str;

class ProductCollectionSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->uploadFiles('product-collections');

        ProductCollection::query()->truncate();

        $productCollections = [
            [
                'name' => 'New Arrival',
            ],
            [
                'name' => 'Best Sellers',
            ],
            [
                'name' => 'Special Offer',
            ],
            [
                'name' => 'Trending Fashion',
            ],
        ];

        ProductCollection::query()->truncate();

        foreach ($productCollections as $key => $item) {
            $item['slug'] = Str::slug($item['name']);
            $item['is_featured'] = true;
            $item['image'] = 'product-collections/' . ($key + 1) . '.jpg';

            ProductCollection::query()->create($item);
        }
    }
}
