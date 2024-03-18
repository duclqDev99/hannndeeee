<?php

namespace Botble\Warehouse\Facades;

use Botble\Warehouse\Supports\MaterialIssueHelper as BaseMaterialIssueHelper;
use Illuminate\Support\Facades\Facade;
class MaterialIssueHelper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BaseMaterialIssueHelper::class;
    }
}
