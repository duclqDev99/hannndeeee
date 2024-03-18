<?php

namespace Botble\WarehouseFinishedProducts\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController as Controller;

abstract class BaseController extends Controller
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans("Kho thành phẩm"));
    }
}
