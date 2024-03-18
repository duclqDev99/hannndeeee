<?php

use Botble\Base\Facades\BaseHelper;
use Botble\CustomLogin\Http\Controllers\Auth\CustomLoginController;
use Illuminate\Support\Facades\Route;
use Botble\Theme\Facades\Theme;

Route::group(['namespace' => 'Botble\CustomLogin\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix()], function () {
        Route::group(['middleware' => 'guest'], function () {
            Route::get('login', [CustomLoginController::class, 'showLoginForm'])->name('access.login');
            Route::post('login', [CustomLoginController::class, 'login'])->name('access.login.post');
            Route::post('/send-otp',[CustomLoginController::class, 'sendOTP'])->name('access.send-otp');
        });

        Route::group(['middleware' => 'auth'], function () {
            Route::get('logout', [
                'as' => 'access.logout',
                'uses' => 'Auth\CustomLoginController@logout',
                'permission' => false,
            ]);
        });
    });
});
if (defined('THEME_MODULE_SCREEN_NAME')) {
        Theme::registerRoutes(function () {
        Route::group([
            'namespace' => 'Botble\CustomLogin\Http\Controllers\Customers',
            'middleware' => ['customer.guest'],
            'as' => 'customer.',
        ], function () {
            Route::get('login', 'LoginController@showLoginForm')->name('login');
            Route::post('login', 'LoginController@login')->name('login.post');
            Route::post('send-otp', 'LoginController@sendOTP')->name('send-otp');
            Route::get('register', 'RegisterController@showRegistrationForm')->name('register');
            Route::post('register', 'RegisterController@register')->name('register.post');

            Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.request');
            Route::post('password/reset', 'ResetPasswordController@reset')->name('password.reset.post');
            Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.reset');
            Route::get('password/reset/{token}', 'ResetPasswordController@showResetForm')
                ->name('password.reset.update');
            Route::get('customer/change-password', [
                'as' => 'change-password',
                'uses' => 'PublicController@getChangePassword',
            ], function(){
                abort(404);
            });
        });
    });
}
