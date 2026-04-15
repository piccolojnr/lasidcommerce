<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyStorefrontSessionConfig
{
    public function handle(Request $request, Closure $next): Response
    {
        config([
            'session.cookie' => config('storefront.session_cookie'),
        ]);

        return $next($request);
    }
}
