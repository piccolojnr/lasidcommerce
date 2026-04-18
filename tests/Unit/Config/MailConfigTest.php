<?php

namespace Tests\Unit\Config;

use Tests\TestCase;

class MailConfigTest extends TestCase
{
    public function test_mail_from_name_falls_back_to_app_name_when_mail_from_name_is_an_unresolved_placeholder(): void
    {
        $this->withEnv('APP_NAME', 'Lasid Commerce');
        $this->withEnv('MAIL_FROM_NAME', '${APP_NAME}');

        $config = require config_path('mail.php');

        $this->assertSame('Lasid Commerce', $config['from']['name']);
    }

    public function test_mail_from_name_uses_explicit_value_when_provided(): void
    {
        $this->withEnv('APP_NAME', 'Lasid Commerce');
        $this->withEnv('MAIL_FROM_NAME', 'Support Desk');

        $config = require config_path('mail.php');

        $this->assertSame('Support Desk', $config['from']['name']);
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
