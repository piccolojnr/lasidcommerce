<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Symfony\Component\HttpFoundation\Cookie;

class StorefrontVerifyCsrfToken extends PreventRequestForgery
{
    protected function getTokenFromRequest($request)
    {
        return $request->input('_token')
            ?: $request->header(config('storefront.csrf_header'))
            ?: $request->header('X-CSRF-TOKEN');
    }

    protected function newCookie($request, $config): Cookie
    {
        return new Cookie(
            config('storefront.csrf_cookie'),
            $request->session()->token(),
            $this->availableAt(60 * $config['lifetime']),
            $config['path'],
            $config['domain'],
            $config['secure'],
            false,
            false,
            $config['same_site'] ?? null,
            $config['partitioned'] ?? false,
        );
    }
}
