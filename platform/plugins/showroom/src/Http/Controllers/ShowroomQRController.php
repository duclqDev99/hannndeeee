<?php

namespace Botble\Showroom\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Http\Controllers\BaseController;

class ShowroomQRController extends BaseController
{
    public function checkQr(){
        Assets::addScriptsDirectly([
            'vendor/core/plugins/showroom/js/check-qr-showroom.js',
            'https://unpkg.com/vue-multiselect@2.1.0',
        ])->addStylesDirectly([
            'https://unpkg.com/vue-multiselect@2.1.0/dist/vue-multiselect.min.css',
        ])->removeItemDirectly([
            'vendor/core/core/media/css/media.css'
        ]);
        Assets::usingVueJS();
        return view('plugins/showroom::check-qr-showroom.check-qr');
    }
}