<?php

namespace Botble\Warehouse\Facades;

use Botble\Warehouse\Services\MaterialFHelper ;
use Illuminate\Support\Facades\Facade;

class MaterialHelper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MaterialFHelper::class;
    }
}
