<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Slug\Facades\SlugHelper;
use Illuminate\Support\Arr;

class ProductCategorySeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->uploadFiles('product-categories');

        $categories = [
            [
                'name' => 'Office bags',
                'image' => 'product-categories/1.png',
                'is_featured' => true,
            ],
            [
                'name' => 'Hand bag',
                'image' => 'product-categories/2.png',
                'is_featured' => true,
            ],
            [
                'name' => 'Woman',
                'image' => 'product-categories/3.png',
                'is_featured' => true,
                'children' => [
                    [
                        'name' => 'Woman wallet',
                    ],
                    [
                        'name' => 'Denim',
                    ],
                    [
                        'name' => 'Dress',
                    ],
                ],
            ],
            [
                'name' => 'Backpack',
                'image' => 'product-categories/4.png',
                'is_featured' => true,
            ],
            [
                'name' => 'Bags',
                'image' => 'product-categories/5.png',
                'is_featured' => true,
            ],
            [
                'name' => 'Wallet',
                'image' => 'product-categories/6.png',
                'is_featured' => true,
            ],
            [
                'name' => 'Men',
                'image' => 'product-categories/7.png',
                'is_featured' => true,
                'children' => [
                    [
                        'name' => 'Accessories',
                    ],
                    [
                        'name' => 'Men wallet',
                    ],
                    [
                        'name' => 'Shoes',
                    ],
                ],
            ],
        ];

        ProductCategory::query()->truncate();

        foreach ($categories as $index => $item) {
            $this->createCategoryItem($index, $item);
        }
    }

    protected function createCategoryItem(int $index, array $category, int|string $parentId = 0): void
    {
        $category['parent_id'] = $parentId;
        $category['order'] = $index + 1;

        if (Arr::has($category, 'children')) {
            $children = $category['children'];
            unset($category['children']);
        } else {
            $children = [];
        }

        $createdCategory = ProductCategory::query()->create($category);

        SlugHelper::createSlug($createdCategory);

        if ($children) {
            foreach ($children as $childIndex => $child) {
                $this->createCategoryItem($childIndex, $child, $createdCategory->id);
            }
        }
    }
}
