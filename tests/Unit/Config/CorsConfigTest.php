<?php

namespace Tests\Unit\Config;

use Tests\TestCase;

class CorsConfigTest extends TestCase
{
    public function test_cors_config_derives_allowed_origins_from_application_urls_and_explicit_overrides(): void
    {
        $this->withEnv('APP_URL', 'https://admin.example.test/dashboard');
        $this->withEnv('STOREFRONT_URL', 'https://shop.example.test:5173/account');
        $this->withEnv('CORS_ALLOWED_ORIGINS', 'https://custom.example.com, http://localhost:3000');

        $config = require config_path('cors.php');

        $this->assertSame([
            'https://custom.example.com',
            'http://localhost:3000',
            'https://admin.example.test',
            'https://shop.example.test:5173',
        ], $config['allowed_origins']);
    }

    public function test_cors_config_filters_invalid_and_duplicate_origins(): void
    {
        $this->withEnv('APP_URL', 'http://backthred.test');
        $this->withEnv('STOREFRONT_URL', 'http://backthred.test');
        $this->withEnv('CORS_ALLOWED_ORIGINS', ' ,not-a-url,http://backthred.test/,http://backthred.test ');

        $config = require config_path('cors.php');

        $this->assertSame([
            'http://backthred.test',
        ], $config['allowed_origins']);
    }

    protected function withEnv(string $key, ?string $value): void
    {
        putenv($value === null ? $key : "{$key}={$value}");

        if ($value === null) {
            unset($_ENV[$key], $_SERVER[$key]);

            return;
        }

        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
