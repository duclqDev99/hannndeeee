<?php

namespace Botble\WarehouseFinishedProducts\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class ApiKeyMiddleware extends Middleware
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
        // Check for Bearer token in Authorization header
        $authorizationHeader = $request->header('Authorization');

        if (!$authorizationHeader || !preg_match('/^Bearer\s+(.*?)$/', $authorizationHeader, $matches)) {
            return response('Unauthorized.', 401);
        }

        $token = $matches[1];

        // Validate the token (you can implement your own validation logic here)
        if ($token && $this->isValidToken($token)) {
            return $next($request);
        }

        return response('Unauthorized.', 401);
    }

    /**
     * Example token validation method.
     * You should replace this with your own validation logic.
     *
     * @param string $token
     * @return bool
     */
    private function isValidToken($token)
    {
        // Your token validation logic goes here
        // For example, check if the token exists in the database or any other validation
        return $token === env('API_KEY');
    }
}
