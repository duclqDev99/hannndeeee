<?php

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Forms\FieldOptions\ImageFieldOption;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Blog\Repositories\Interfaces\PostInterface;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\FlashSale;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Repositories\Interfaces\ProductInterface;
use Botble\Faq\Models\FaqCategory;
use Botble\Faq\Repositories\Interfaces\FaqCategoryInterface;
use Botble\Shortcode\Compilers\Shortcode;
use Botble\Shortcode\Facades\Shortcode as ShortcodeFacade;
use Botble\Shortcode\Forms\ShortcodeForm;
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
                $products = get_featured_products_for_homepage(
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
                $products = get_trending_products_homepage(
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

    add_shortcode('slider-banner', __('Slider banner'), __('Slider banner'), function (Shortcode $shortcode) {
        return Theme::partial('short-codes.slider-banner', [
            'title' => $shortcode->title,
        ]);
    });

    add_shortcode('seasonal-product', __('Seasional product'), __('Seasional product'), function (Shortcode $shortcode) {
        return Theme::partial('short-codes.seasonal-product', [
            'title' => $shortcode->title,
            'subtitle' => $shortcode->subtitle,
            'imageBanner' => $shortcode->imagebanner,
        ]);
    });

    shortcode()->setAdminConfig('seasonal-product', function (array $attributes) {
        return Theme::partial('short-codes.seasonal-product-admin-config', compact('attributes'));
    });

    add_shortcode('uniform-collection', __('Uniform collection'), __('Uniform collection'), function (Shortcode $shortcode) {
        $products = get_products_uniform_collection();
        return Theme::partial('short-codes.uniform-collection', [
            'title' => $shortcode->title,
            'keytitle' => $shortcode->keytitle,
            'subtitle' => $shortcode->subtitle,
            'products' => $products
        ]);
    });

    add_shortcode(
        'feature-product-banner',
        __('Featured products banner'),
        __('Featured products banner'),
        function (Shortcode $shortcode) {
            $products = get_featured_products_for_homepage(
                [
                    'take' => (int)$shortcode->limit ?: 8,
                    'with' => EcommerceHelper::withProductEagerLoadingRelations(),
                ] + EcommerceHelper::withReviewsParams()
            );

            return Theme::partial('short-codes.feature-product-banner', [
                'title' => $shortcode->title,
                'url' => $shortcode->url ?? route('public.products'),
                'imageBanner' => $shortcode->imagebanner,
                'titleFeature' => $shortcode->titlefeature,
                'imageFeature' => $shortcode->imagefeature,
                'products' => $products,
            ]);
        }
    );

    shortcode()->setAdminConfig('feature-product-banner', function (array $attributes) {
        return Theme::partial('short-codes.feature-product-banner-admin-config', compact('attributes'));
    });

    add_shortcode('order-customer', __('Form đặt hàng'), __('Form đặt hàng của khách hàng'), function (Shortcode $shortcode) {
        return Theme::partial('short-codes.order-customer', [
            'title' => $shortcode->title,
            'subtitle' => $shortcode->subtitle,
        ]);
    });

    add_shortcode('component-contact', __('Liên hệ với chúng tôi'), __('Liên hệ với chúng tôi'), function (Shortcode $shortcode) {
        return Theme::partial('short-codes.component-contact', [
            'title' => $shortcode->title,
            'imageBanner' => $shortcode->imagebanner,
            'imageFeature' => $shortcode->imagefeature,
        ]);
    });

    shortcode()->setAdminConfig('component-contact', function (array $attributes) {
        return Theme::partial('short-codes.component-contact-admin-config', compact('attributes'));
    });

    add_shortcode('statistical', __('Thống kê'), __('Thống kê các doanh mục cho website'), function (Shortcode $shortcode) {
        return Theme::partial('short-codes.statistical', [
            'title' => $shortcode->title ?? '',
            'imageBanner' => $shortcode->imagebanner ?? ''
        ]);
    });
    
    shortcode()->setAdminConfig('statistical', function (array $attributes) {
        return Theme::partial('short-codes.statistical-admin-config', compact('attributes'));
    });

    shortcode()
        ->register(
            'widget-uniform',
            __('Bộ sưu tập cho Nam/Nữ'),
            __('Bộ sưu tập cho Nam/Nữ'),
            function ($shortcode) {
                $titleMan = $shortcode->title_man ?? '';
                $subtitleMan = $shortcode->subtitle_man ?? '';
                $imgMan = $shortcode->image_man ?? '';
                $urlMan = $shortcode->url_man ?? '';

                $titleGirl = $shortcode->title_girl ?? '';
                $subtitleGirl = $shortcode->subtitle_girl ?? '';
                $imgGirl = $shortcode->image_girl ?? '';
                $urlGirl = $shortcode->url_girl ?? '';

                return Theme::partial('short-codes.widget-uniform', compact('titleMan', 'subtitleMan', 'imgMan', 'urlMan', 'titleGirl', 'subtitleGirl', 'imgGirl', 'urlGirl'));
            }
        )
        ->setAdminConfig('widget-uniform', function ($attributes) {
            return ShortcodeForm::createFromArray($attributes)
                ->add('title_man', TextField::class, [
                    'label' => __('Tiêu đề 1'),
                ])
                ->add('subtitle_man', TextField::class, [
                    'label' => __('Phụ đề 1'),
                ])
                ->add('image_man', MediaImageField::class, [
                    'label' => __('Hình ảnh 1'),
                ])
                ->add('url_man', TextField::class, [
                    'label' => __('Đường dẫn 1'),
                ])
                ->add('title_girl', TextField::class, [
                    'label' => __('Tiêu đề 2'),
                ])
                ->add('subtitle_girl', TextField::class, [
                    'label' => __('Phụ đề 2'),
                ])
                ->add('image_girl', MediaImageField::class, [
                    'label' => __('Hình ảnh 2'),
                ])
                ->add('url_girl', TextField::class, [
                    'label' => __('Đường dẫn 2'),
                ])
                ;
        });


    shortcode()
        ->register(
            'life-style',
            __('Lift Style'),
            __('Lift Style'),
            function ($shortcode) {
                $title = $shortcode->title ?? '';
                $subtitle = $shortcode->subtitle ?? '';
                $note = $shortcode->note ?? '';
                $textBtn = $shortcode->text_btn ?? '';
                $url = $shortcode->url ?? '';
                $image = $shortcode->image ?? '';
                
                return Theme::partial('short-codes.life-style', compact('title', 'subtitle', 'note', 'textBtn', 'url', 'image'));
            }
        )
        ->setAdminConfig('life-style', function ($attributes) {
            return ShortcodeForm::createFromArray($attributes)
                ->add('title', TextField::class, [
                    'label' => __('Tiêu đề'),
                ])
                ->add('subtitle', TextField::class, [
                    'label' => __('Phụ đề'),
                ])
                ->add('note', TextareaField::class, [
                    'label' => __('Nội dung'),
                ])
                ->add('text_btn', TextField::class, [
                    'label' => __('Text button'),
                ])
                ->add('url', TextField::class, [
                    'label' => __('Đường dẫn'),
                ])
                ->add('image', MediaImageField::class, [
                    'label' => __('Hình ảnh'),
                ])
                ;
        });
});
