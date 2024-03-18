<?php

use Botble\Slug\Facades\SlugHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Theme\Tomorow\Http\Controllers', 'middleware' => 'web'], function () {
    Route::group(apply_filters(BASE_FILTER_GROUP_PUBLIC_ROUTE, []), function () {
        Route::get('ajax/cart', [
            'as' => 'public.ajax.cart',
            'uses' => 'SeptemberController@ajaxCart',
        ]);

        Route::get('products/seasonal', [
            'as' => 'public.products.seasonal',
            'uses' => 'SeptemberController@productsSeasonal'
        ]);
    });
});

// Route::group(['namespace' => 'Theme\Tomorow\Http\Controllers'], function () {
//     Theme::registerRoutes(function () {
//         Route::get('order',
//             [
//                 'uses' => 'SeptemberController@getViewOrderUniformForCustomer',
//                 'as' => 'public.order',
//             ]
//         );
//     });
// });

Theme::routes();
