<?php

namespace Botble\OrderTransaction\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class TransactionApiTrust extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$guards
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        return $next($request);

        if ($request->getHost() === env('HOST_TRANSACTION_API_TRUST')) {
            return $next($request);
        }
        return response('Unauthorized.', 401);
    }



}
