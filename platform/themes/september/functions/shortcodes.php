<?php

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Blog\Repositories\Interfaces\PostInterface;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\FlashSale;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Repositories\Interfaces\ProductInterface;
use Botble\Faq\Models\FaqCategory;
use Botble\Faq\Repositories\Interfaces\FaqCategoryInterface;
use Botble\Shortcode\Compilers\Shortcode;
use Botble\Shortcode\Facades\Shortcode as ShortcodeFacade;
use Botble\Theme\Facades\Theme;
use Botble\Theme\Supports\ThemeSupport;

app()->booted(function () {
    ThemeSupport::registerGoogleMapsShortcode();
    ThemeSupport::registerYoutubeShortcode();

    if (is_plugin_active('ecommerce')) {
        add_shortcode(
            'product-categories',
            __('Product categories'),
            __('Product categories'),
            function (Shortcode $shortcode) {
                return Theme::partial('short-codes.product-categories', [
                    'title' => $shortcode->title,
                    'description' => $shortcode->description,
                    'subtitle' => $shortcode->subtitle,
                ]);
            }
        );

        shortcode()->setAdminConfig('product-categories', function (array $attributes) {
            return Theme::partial('short-codes.product-categories-admin-config', compact('attributes'));
        });

        add_shortcode(
            'featured-products',
            __('Featured products'),
            __('Featured products'),
            function (Shortcode $shortcode) {
                $products = get_featured_products(
                    [
                        'take' => (int)$shortcode->limit ?: 8,
                        'with' => EcommerceHelper::withProductEagerLoadingRelations(),
                    ] + EcommerceHelper::withReviewsParams()
                );

                return Theme::partial('short-codes.featured-products', [
                    'title' => $shortcode->title,
                    'description' => $shortcode->description,
                    'subtitle' => $shortcode->subtitle,
                    'products' => $products,
                ]);
            }
        );

        shortcode()->setAdminConfig('featured-products', function (array $attributes) {
            return Theme::partial('short-codes.featured-products-admin-config', compact('attributes'));
        });

        add_shortcode(
            'featured-product-categories',
            __('Featured Product Categories'),
            __('Featured Product Categories'),
            function (Shortcode $shortcode) {
                $categories = get_featured_product_categories();

                return Theme::partial('short-codes.featured-product-categories', [
                    'title' => $shortcode->title,
                    'description' => $shortcode->description,
                    'subtitle' => $shortcode->subtitle,
                    'categories' => $categories,
                ]);
            }
        );

        shortcode()->setAdminConfig('featured-product-categories', function (array $attributes) {
            return Theme::partial('short-codes.featured-product-categories-admin-config', compact('attributes'));
        });

        add_shortcode('featured-brands', __('Featured Brands'), __('Featured Brands'), function (Shortcode $shortcode) {
            $brands = get_featured_brands();

            return Theme::partial('short-codes.featured-brands', [
                'title' => $shortcode->title,
                'brands' => $brands,
            ]);
        });

        shortcode()->setAdminConfig('featured-brands', function (array $attributes) {
            return Theme::partial('short-codes.featured-brands-admin-config', compact('attributes'));
        });

        add_shortcode(
            'product-collections',
            __('Product collections'),
            __('Product collections'),
            function (Shortcode $shortcode) {
                return Theme::partial('short-codes.product-collections', [
                    'title' => $shortcode->title,
                    'description' => $shortcode->description,
                    'subtitle' => $shortcode->subtitle,
                ]);
            }
        );

        shortcode()->setAdminConfig('product-collections', function (array $attributes) {
            return Theme::partial('short-codes.product-collections-admin-config', compact('attributes'));
        });

        add_shortcode(
            'trending-products',
            __('Trending Products'),
            __('Trending Products'),
            function (Shortcode $shortcode) {
                $products = get_trending_products(
                    [
                        'take' => (int)$shortcode->limit ?: 4,
                        'with' => EcommerceHelper::withProductEagerLoadingRelations(),
                    ] + EcommerceHelper::withReviewsParams()
                );

                return Theme::partial('short-codes.trending-products', [
                    'title' => $shortcode->title,
                    'description' => $shortcode->description,
                    'subtitle' => $shortcode->subtitle,
                    'products' => $products,
                ]);
            }
        );

        shortcode()->setAdminConfig('trending-products', function (array $attributes) {
            return Theme::partial('short-codes.trending-products-admin-config', compact('attributes'));
        });

        add_shortcode('all-products', __('All Products'), __('All Products'), function (Shortcode $shortcode) {
            $categoryIds = ShortcodeFacade::fields()->getIds('category_ids', $shortcode);

            $products = app(ProductInterface::class)->filterProducts(
                [
                    'categories' => $categoryIds,
                ],
                [
                    'condition' => [
                        'ec_products.is_variation' => 0,
                    ],
                    'order_by' => [
                        'ec_products.order' => 'ASC',
                        'ec_products.created_at' => 'DESC',
                    ],
                    'take' => null,
                    'select' => [
                        'ec_products.*',
                    ],
                    'with' => ['slugable'],
                    'paginate' => [
                        'per_page' => (int)($shortcode->per_page ?: 12),
                        'current_paged' => (int)request()->input('page') ?: 1,
                    ],
                ] + EcommerceHelper::withReviewsParams()
            );

            return Theme::partial('short-codes.all-products', [
                'title' => $shortcode->title,
                'subtitle' => $shortcode->subtitle,
                'products' => $products,
            ]);
        });

        shortcode()->setAdminConfig('all-products', function (array $attributes) {
            $categories = ProductCategory::query()
                ->wherePublished()
                ->select(['id', 'name', 'parent_id'])
                ->get();

            return Theme::partial('short-codes.all-products-admin-config', compact('attributes', 'categories'));
        });

        add_shortcode('all-brands', __('All Brands'), __('All Brands'), function (Shortcode $shortcode) {
            $brands = get_all_brands();

            return Theme::partial('short-codes.all-brands', [
                'title' => $shortcode->title,
                'subtitle' => $shortcode->subtitle,
                'brands' => $brands,
            ]);
        });

        shortcode()->setAdminConfig('all-brands', function (array $attributes) {
            return Theme::partial('short-codes.all-brands-admin-config', compact('attributes'));
        });

        add_shortcode('flash-sale', __('Flash sale'), __('Flash sale'), function (Shortcode $shortcode) {
            $flashSales = FlashSale::query()
                ->notExpired()
                ->where('status', BaseStatusEnum::PUBLISHED)
                ->with([
                    'products' => function ($query) use ($shortcode) {
                        return $query
                            ->where('status', BaseStatusEnum::PUBLISHED)
                            ->limit((int)$shortcode->limit ?: 2)
                            ->withCount(EcommerceHelper::withReviewsParams()['withCount'])
                            ->with(EcommerceHelper::withProductEagerLoadingRelations());
                    },
                ])
                ->get();

            if (! $flashSales->count()) {
                return null;
            }

            return Theme::partial('short-codes.flash-sale', [
                'title' => $shortcode->title,
                'subtitle' => $shortcode->subtitle,
                'showPopup' => $shortcode->show_popup,
                'limit' => $shortcode->limit ?: 2,
                'flashSales' => $flashSales,
            ]);
        });

        shortcode()->setAdminConfig('flash-sale', function (array $attributes) {
            return Theme::partial('short-codes.flash-sale-admin-config', compact('attributes'));
        });
    }

    if (is_plugin_active('blog')) {
        add_shortcode('news', __('News'), __('News'), function (Shortcode $shortcode) {
            $posts = app(PostInterface::class)->getFeatured((int)$shortcode->limit ?: 3, ['slugable']);

            return Theme::partial('short-codes.news', [
                'title' => $shortcode->title,
                'description' => $shortcode->description,
                'subtitle' => $shortcode->subtitle,
                'posts' => $posts,
            ]);
        });
        shortcode()->setAdminConfig('news', function (array $attributes) {
            return Theme::partial('short-codes.news-admin-config', compact('attributes'));
        });
    }

    if (is_plugin_active('contact')) {
        add_filter(CONTACT_FORM_TEMPLATE_VIEW, function () {
            return Theme::getThemeNamespace() . '::partials.short-codes.contact-form';
        }, 120);
    }

    if (is_plugin_active('simple-slider')) {
        add_filter(SIMPLE_SLIDER_VIEW_TEMPLATE, function () {
            return Theme::getThemeNamespace() . '::partials.short-codes.sliders';
        }, 120);
    }

    add_shortcode(
        'our-features',
        __('Our features (deprecated)'),
        __('Our features (deprecated)'),
        function (Shortcode $shortcode) {
            $items = $shortcode->items;
            $items = explode(';', $items);
            $data = [];
            foreach ($items as $item) {
                $data[] = json_decode(trim($item), true);
            }

            return Theme::partial('short-codes.our-features', compact('data'));
        }
    );

    add_shortcode('site-features', __('Site features'), __('Site features'), function (Shortcode $shortcode) {
        return Theme::partial('short-codes.site-features', compact('shortcode'));
    });

    shortcode()->setAdminConfig('site-features', function (array $attributes) {
        return Theme::partial('short-codes.site-features-admin-config', compact('attributes'));
    });

    if (is_plugin_active('gallery')) {
        add_shortcode(
            'theme-galleries',
            __('Galleries (HASA theme)'),
            __('Galleries images'),
            function (Shortcode $shortcode) {
                return Theme::partial('short-codes.galleries', [
                    'title' => $shortcode->title,
                    'description' => $shortcode->description,
                    'subtitle' => $shortcode->subtitle,
                    'limit' => (int)$shortcode->limit ?: 8,
                ]);
            }
        );

        shortcode()->setAdminConfig('theme-galleries', function (array $attributes) {
            return Theme::partial('short-codes.galleries-admin-config', compact('attributes'));
        });
    }

    if (is_plugin_active('faq')) {
        add_shortcode('faqs', __('FAQs'), __('List of FAQs'), function (Shortcode $shortcode) {
            $params = [
                'condition' => [
                    'status' => BaseStatusEnum::PUBLISHED,
                ],
                'with' => [
                    'faqs' => function ($query) {
                        $query->where('status', BaseStatusEnum::PUBLISHED);
                    },
                ],
                'order_by' => [
                    'faq_categories.order' => 'ASC',
                    'faq_categories.created_at' => 'DESC',
                ],
            ];

            if ($shortcode->category_id) {
                $params['condition']['id'] = $shortcode->category_id;
            }

            $categories = app(FaqCategoryInterface::class)->advancedGet($params);

            return Theme::partial('short-codes.faqs', compact('categories'));
        });

        shortcode()->setAdminConfig('faqs', function (array $attributes) {
            $categories = FaqCategory::query()
                ->where(['status' => BaseStatusEnum::PUBLISHED])
                ->pluck('name', 'id')
                ->all();

            return Theme::partial('short-codes.faqs-admin-config', compact('attributes', 'categories'));
        });
    }
});
