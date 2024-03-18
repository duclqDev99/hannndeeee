<?php


use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\SortItemsWithChildrenHelper;

use Botble\Warehouse\Repositories\Interfaces\CategoryInterface;

use Illuminate\Support\Arr;

use Botble\Warehouse\Repositories\Interfaces\MaterialInterface;
use Botble\Warehouse\Repositories\Interfaces\TypeMaterialInterface;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

if (!function_exists('get_warehouse_categories')) {
    function get_warehouse_categories(array $args = []): array
    {
        $indent = Arr::get($args, 'indent', '——');

        $repo = app(CategoryInterface::class);

        $categories = $repo->getCategories(Arr::get($args, 'select', ['*']), [
            'created_at' => 'DESC',
            'order' => 'ASC',
        ], Arr::get($args, 'condition', ['status' => BaseStatusEnum::PUBLISHED]));

        $categories = sort_item_with_children($categories);

        foreach ($categories as $category) {
            $depth = (int) $category->depth;
            $indentText = str_repeat($indent, $depth);
            $category->indent_text = $indentText;
        }

        return $categories;
    }
}


if (!function_exists('get_finished_categories_with_children')) {
    function get_finished_categories_with_children(): array
    {
        $categories = app(CategoryInterface::class)
            ->getAllCategoriesWithChildren(['status' => BaseStatusEnum::PUBLISHED], [], ['id', 'name', 'parent_id']);

        return app(SortItemsWithChildrenHelper::class)
            ->setChildrenProperty('child_cats')
            ->setItems($categories)
            ->sort();
    }
}

if (!function_exists('get_branch_id_by_user')) {
    function get_branch_id_by_user()
    {
        $user = request()->user();
        if ($user->isSuperUser()) {
            return 0;
        }
        return $user->branch_id;
    }
}


if (!function_exists('get_featured_posts')) {
    function get_featured_posts(int $limit, array $with = []): Collection
    {
        return app(MaterialInterface::class)->getFeatured($limit, $with);
    }
}

if (!function_exists('get_latest_posts')) {
    function get_latest_posts(int $limit, array $excepts = [], array $with = []): Collection
    {
        return app(MaterialInterface::class)->getListPostNonInList($excepts, $limit, $with);
    }
}

if (!function_exists('get_related_posts')) {
    function get_related_posts(int|string $id, int $limit): Collection
    {
        return app(MaterialInterface::class)->getRelated($id, $limit);
    }
}

if (!function_exists('get_posts_by_category')) {
    function get_posts_by_category(int|string $categoryId, int $paginate = 12, int $limit = 0): Collection|LengthAwarePaginator
    {
        return app(MaterialInterface::class)->getByCategory($categoryId, $paginate, $limit);
    }
}

if (!function_exists('get_posts_by_tag')) {
    function get_posts_by_tag(string $slug, int $paginate = 12): Collection|LengthAwarePaginator
    {
        return app(MaterialInterface::class)->getByTag($slug, $paginate);
    }
}

if (!function_exists('get_posts_by_user')) {
    function get_posts_by_user(int|string $authorId, int $paginate = 12): Collection|LengthAwarePaginator
    {
        return app(MaterialInterface::class)->getByUserId($authorId, $paginate);
    }
}

if (!function_exists('get_all_posts')) {
    function get_all_posts(
        bool $active = true,
        int $perPage = 12,
        array $with = ['slugable', 'categories', 'categories.slugable', 'author']
    ): Collection|LengthAwarePaginator {
        return app(MaterialInterface::class)->getAllPosts($perPage, $active, $with);
    }
}

if (!function_exists('get_recent_posts')) {
    function get_recent_posts(int $limit): Collection|LengthAwarePaginator
    {
        return app(MaterialInterface::class)->getRecentPosts($limit);
    }
}

if (!function_exists('get_featured_categories')) {
    function get_featured_categories(int $limit, array $with = []): Collection|LengthAwarePaginator
    {
        return app(TypeMaterialInterface::class)->getFeaturedCategories($limit, $with);
    }
}

if (!function_exists('get_all_categories')) {
    function get_all_categories(array $condition = [], array $with = []): Collection|LengthAwarePaginator
    {
        return app(TypeMaterialInterface::class)->getAllCategories($condition, $with);
    }
}





if (!function_exists('get_popular_posts')) {
    function get_popular_posts(int $limit = 10, array $args = []): Collection|LengthAwarePaginator
    {
        return app(MaterialInterface::class)->getPopularPosts($limit, $args);
    }
}


if (!function_exists('get_typematerial')) {
    function get_typematerial(array $args = []): array
    {
        $repo = app(TypeMaterialInterface::class);
        $indent = Arr::get($args, 'indent', '—');
        $materials = $repo->getTypeMaterial(
            Arr::get($args, 'select', ['*']),
            [
                'is_default' => 'DESC',
                'created_at' => 'DESC',
            ],
            Arr::get($args, 'condition', ['status' => BaseStatusEnum::PUBLISHED])
        );

        $materials = sort_item_with_children($materials);

        foreach ($materials as $material) {
            $depth = (int) $material->depth;
            $indentText = str_repeat($indent, $depth);
            $material->indent_text = $indentText;
        }

        return $materials;
    }
}


if (!function_exists('get_typematerial_with_children')) {
    function get_typematerial_with_children(): array
    {
        $materials = app(TypeMaterialInterface::class)
            ->getAllMaterialsWithChildren(['status' => BaseStatusEnum::PUBLISHED], [], ['id', 'name', 'parent_id']);
        return app(SortItemsWithChildrenHelper::class)
            ->setChildrenProperty('child_cats')
            ->setItems($materials)
            ->sort();
    }
}

if (!function_exists('get_warehouse_categories')) {
    function get_warehouse_categories(array $args = []): array
    {
        $indent = Arr::get($args, 'indent', '——');

        $repo = app(CategoryInterface::class);

        $categories = $repo->getCategories(Arr::get($args, 'select', ['*']), [
            'created_at' => 'DESC',
            'order' => 'ASC',
        ], Arr::get($args, 'condition', ['status' => BaseStatusEnum::PUBLISHED]));

        $categories = sort_item_with_children($categories);

        foreach ($categories as $category) {
            $depth = (int) $category->depth;
            $indentText = str_repeat($indent, $depth);
            $category->indent_text = $indentText;
        }

        return $categories;
    }
}


if (!function_exists('get_finished_categories_with_children')) {
    function get_finished_categories_with_children(): array
    {
        $categories = app(CategoryInterface::class)
            ->getAllCategoriesWithChildren(['status' => BaseStatusEnum::PUBLISHED], [], ['id', 'name', 'parent_id']);

        return app(SortItemsWithChildrenHelper::class)
            ->setChildrenProperty('child_cats')
            ->setItems($categories)
            ->sort();
    }
}


if (!function_exists('get_inventory_id_by_user')) {
    function get_inventory_id_by_user()
    {
        $user = request()->user();
        if ($user->isSuperUser()) {
            return 0;
        }
        return $user->inventory_id;
    }
}
if (!function_exists('response_with_messages')) {
    function response_with_messages(string|array $messages, bool $error = false, int $responseCode = null, array|string|null $data = null): array
    {
        return [
            'error' => $error,
            'response_code' => $responseCode ?: 200,
            'messages' => (array) $messages,
            'data' => $data,
        ];
    }
}

if (!function_exists('get_material_stock_id_by_request')) {
    function get_material_stock_id_by_request()
    {
        if (isset(request()->input()['id'])) {
            return request()->input()['id'];
        }
        return;
    }
}

if (!function_exists('convert_number_to_words')) {
    function convert_number_to_words($number)
    {
        $hyphen = ' ';
        $conjunction = ' ';
        $separator = ' ';
        $negative = 'âm ';
        $decimal = ' phẩy ';
        $dictionary = array(
            0 => 'không',
            1 => 'một',
            2 => 'hai',
            3 => 'ba',
            4 => 'bốn',
            5 => 'năm',
            6 => 'sáu',
            7 => 'bảy',
            8 => 'tám',
            9 => 'chín',
            10 => 'mười',
            11 => 'mười một',
            12 => 'mười hai',
            13 => 'mười ba',
            14 => 'mười bốn',
            15 => 'mười năm',
            16 => 'mười sáu',
            17 => 'mười bảy',
            18 => 'mười tám',
            19 => 'mười chín',
            20 => 'hai mươi',
            30 => 'ba mươi',
            40 => 'bốn mươi',
            50 => 'năm mươi',
            60 => 'sáu mươi',
            70 => 'bảy mươi',
            80 => 'tám mươi',
            90 => 'chín mươi',
            100 => 'trăm',
            1000 => 'nghìn',
            1000000 => 'triệu',
            1000000000 => 'tỷ',
            1000000000000 => 'nghìn tỷ',
            1000000000000000 => 'nghìn triệu triệu',
            1000000000000000000 => 'tỷ tỷ'
        );
        if (!is_numeric($number)) {
            return false;
        }
        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }
        if ($number < 0) {
            return $negative . convert_number_to_words(abs($number));
        }
        $string = $fraction = null;
        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }
        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens = ((int) ($number / 10)) * 10;
                $units = $number % 10;
                $string = $dictionary[$tens];
                if ($units == 1) {
                    $string .= $hyphen . 'mốt';
                } else if ($units == 5) {
                    $string .= $hyphen . 'lăm';
                } else if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    if ($remainder < 10) {
                        $string .= ' lẻ';
                    }
                    $string .= $conjunction . convert_number_to_words($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    if ($remainder < 100) {
                        $string .= ' không trăm';
                    }

                    $string .= ' ' . convert_number_to_words($remainder);
                }
                break;
        }
        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }
        return $string;
    }
}

