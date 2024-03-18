<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Language\Models\LanguageMeta;
use Botble\Menu\Facades\Menu;
use Botble\Menu\Models\Menu as MenuModel;
use Botble\Menu\Models\MenuLocation;
use Botble\Menu\Models\MenuNode;
use Botble\Page\Models\Page;
use Illuminate\Support\Arr;

class MenuSeeder extends BaseSeeder
{
    public function run(): void
    {
        $data = [
            [
                'name' => 'Main menu',
                'slug' => 'main-menu',
                'location' => 'main-menu',
                'items' => [
                    [
                        'title' => 'Home',
                        'url' => '/',
                    ],
                    [
                        'title' => 'Products',
                        'url' => '/products',
                        'children' => [
                            [
                                'title' => 'Woman wallet',
                                'reference_id' => 1,
                                'reference_type' => ProductCategory::class,
                            ],
                            [
                                'title' => 'Office bags',
                                'reference_id' => 2,
                                'reference_type' => ProductCategory::class,
                            ],
                            [
                                'title' => 'Hand bag',
                                'reference_id' => 3,
                                'reference_type' => ProductCategory::class,
                            ],
                            [
                                'title' => 'Backpack',
                                'reference_id' => 4,
                                'reference_type' => ProductCategory::class,
                            ],
                        ],
                    ],
                    [
                        'title' => 'Blog',
                        'reference_id' => 2,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => 'FAQ',
                        'reference_id' => 5,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => 'Contact',
                        'reference_id' => 3,
                        'reference_type' => Page::class,
                    ],
                ],
            ],
            [
                'name' => 'Customer services',
                'slug' => 'customer-services',
                'items' => [
                    [
                        'title' => 'Login',
                        'url' => '/login',
                    ],
                    [
                        'title' => 'Register',
                        'url' => '/register',
                    ],
                    [
                        'title' => 'Blog',
                        'reference_id' => 2,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => 'Contact',
                        'reference_id' => 3,
                        'reference_type' => Page::class,
                    ],
                ],
            ],
        ];

        MenuModel::query()->truncate();
        MenuLocation::query()->truncate();
        MenuNode::query()->truncate();

        foreach ($data as $index => $item) {
            $menu = MenuModel::query()->create(Arr::except($item, ['items', 'location']));

            if (isset($item['location'])) {
                $menuLocation = MenuLocation::query()->create([
                    'menu_id' => $menu->id,
                    'location' => $item['location'],
                ]);

                LanguageMeta::saveMetaData($menuLocation);
            }

            foreach ($item['items'] as $menuNode) {
                $this->createMenuNode($index, $menuNode, 'en_US', $menu->id);
            }

            LanguageMeta::saveMetaData($menu);
        }

        Menu::clearCacheMenuItems();
    }

    protected function createMenuNode(
        int $index,
        array $menuNode,
        string $locale,
        int|string $menuId,
        int|string $parentId = 0
    ): void {
        $menuNode['menu_id'] = $menuId;
        $menuNode['parent_id'] = $parentId;

        if (isset($menuNode['url'])) {
            $menuNode['url'] = str_replace(url(''), '', $menuNode['url']);
        }

        if (Arr::has($menuNode, 'children')) {
            $children = $menuNode['children'];
            $menuNode['has_child'] = true;

            unset($menuNode['children']);
        } else {
            $children = [];
            $menuNode['has_child'] = false;
        }

        $createdNode = MenuNode::query()->create($menuNode);

        if ($children) {
            foreach ($children as $child) {
                $this->createMenuNode($index, $child, $locale, $menuId, $createdNode->id);
            }
        }
    }
}
