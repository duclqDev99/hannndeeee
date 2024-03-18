<?php

namespace Botble\WarehouseFinishedProducts\Services\Abstracts;

use Botble\ACL\Models\User;
use Illuminate\Http\Request;

abstract class StoreUserWarehouseServiceAbstract
{
    public function __construct()
    {
    }

    abstract public function execute(Request $request, User $post): void;
}
