<?php

namespace Botble\Warehouse\Services\Abstracts;

use Botble\Warehouse\Models\Material;
use Illuminate\Http\Request;

abstract class StoreTypeMaterialServiceAbstract
{
    public function __construct()
    {
    }

    abstract public function execute(Request $request, Material $post): void;
}
