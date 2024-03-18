<?php

namespace Botble\Warehouse\Http\Controllers\API;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Warehouse\Models\ProcessingHouse;

class ProcessHouseApiController extends BaseController
{
    public function getAllProcessHouse ()
    {
        $processinghouse = ProcessingHouse::where('status','active')->get();
        return response()->json(['process' => $processinghouse],200);
    }
}
