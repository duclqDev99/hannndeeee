<?php

namespace Botble\HubWarehouse\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController as Controller;

abstract class BaseController extends Controller
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans("HUB"));
    }
}
