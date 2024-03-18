<?php

namespace Botble\SaleWarehouse\Services\Abstracts;

use Botble\ACL\Models\User;
use Illuminate\Http\Request;

abstract class StoreUserSaleServiceAbstract
{
    public function __construct()
    {
    }

    abstract public function execute(Request $request, User $post): void;
}
