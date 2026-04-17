<?php

$normalizeOrigin = static function (?string $url): ?string {
    if (! is_string($url) || trim($url) === '') {
        return null;
    }

    $parts = parse_url(trim($url));

    if ($parts === false || ! isset($parts['scheme'], $parts['host'])) {
        return null;
    }

    $origin = strtolower($parts['scheme']).'://'.$parts['host'];

    if (isset($parts['port'])) {
        $origin .= ':'.$parts['port'];
    }

    return $origin;
};

$configuredOrigins = array_map(
    'trim',
    explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))
);

$derivedOrigins = array_filter([
    $normalizeOrigin(env('APP_URL')),
    $normalizeOrigin(env('STOREFRONT_URL')),
]);

$allowedOrigins = array_values(array_unique(array_filter([
    ...array_map($normalizeOrigin, $configuredOrigins),
    ...$derivedOrigins,
])));

return [
    'paths' => [
        'api/*',
        'api/v1/*',
        'sanctum/csrf-cookie',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => env('CORS_SUPPORTS_CREDENTIALS', true),
];
