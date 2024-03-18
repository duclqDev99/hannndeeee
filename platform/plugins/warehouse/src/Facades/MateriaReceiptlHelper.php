<?php

namespace Botble\Warehouse\Facades;

use Botble\Warehouse\Supports\MaterialReceiptHelper as BaseMaterialReceiptHelper;
use Illuminate\Support\Facades\Facade;
class MaterialReceiptHelper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BaseMaterialReceiptHelper::class;
    }
}
