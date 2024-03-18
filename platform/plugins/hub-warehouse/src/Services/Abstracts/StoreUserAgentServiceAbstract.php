<?php

namespace Botble\HubWarehouse\Services\Abstracts;

use Botble\ACL\Models\User;
use Illuminate\Http\Request;

abstract class StoreUserAgentServiceAbstract
{
    public function __construct()
    {
    }

    abstract public function execute(Request $request, User $post): void;
}
