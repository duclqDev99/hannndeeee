<?php

namespace Botble\Warehouse\Services;

use Botble\Warehouse\Services\Abstracts\StoreTypeMaterialServiceAbstract;
use Botble\Warehouse\Models\Material;
use Illuminate\Http\Request;

class StorageTypeMaterialService extends StoreTypeMaterialServiceAbstract
{
    public function execute(Request $request, Material $material): void
    {
        $material->type_materials()->sync($request->input('type_materials', []));
    }
}
