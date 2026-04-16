<?php

return [
    'url' => env('STOREFRONT_URL', env('APP_URL', 'http://localhost')),
    'session_cookie' => env('STOREFRONT_SESSION_COOKIE', 'storefront_session'),
    'session_domain' => env('STOREFRONT_SESSION_DOMAIN', null),
    'csrf_cookie' => env('STOREFRONT_CSRF_COOKIE', 'XSRF-STOREFRONT-TOKEN'),
    'csrf_header' => env('STOREFRONT_CSRF_HEADER', 'X-STOREFRONT-CSRF-TOKEN'),
    'default_redirect_path' => env('STOREFRONT_DEFAULT_REDIRECT_PATH', '/account'),
    'orders_path' => env('STOREFRONT_ORDERS_PATH', '/account/orders'),
    'auth_error_redirect_path' => env('STOREFRONT_AUTH_ERROR_REDIRECT_PATH', '/auth'),
    'magic_link_expire_minutes' => (int) env('STOREFRONT_MAGIC_LINK_EXPIRE_MINUTES', 30),
];
