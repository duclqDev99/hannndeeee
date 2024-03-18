<?php

namespace Botble\Showroom\Services\Abstracts;

use Botble\ACL\Models\User;
use Illuminate\Http\Request;

abstract class StoreUserShowroomServiceAbstract
{
    public function __construct()
    {
    }

    abstract public function execute(Request $request, User $post): void;
}
