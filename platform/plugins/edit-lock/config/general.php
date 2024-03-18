<?php

return [
    'supported' => [
        'page' => Botble\Page\Models\Page::class,
        'post' => Botble\Blog\Models\Post::class,
        'product' => Botble\Ecommerce\Models\Product::class,
    ],
    'use_cache' => true,
    'interval' => 90,
];