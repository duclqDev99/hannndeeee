<?php

namespace Botble\Ecommerce\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfCustomer
{
    public function handle(Request $request, Closure $next, string $guard = 'customer')
    {
        if (Auth::guard($guard)->check()) {
            return redirect(route('public.index'));
        }

        return $next($request);
    }
}
