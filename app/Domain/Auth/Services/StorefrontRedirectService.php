<?php

namespace App\Domain\Auth\Services;

class StorefrontRedirectService
{
    public function toSuccessUrl(?string $path = null): string
    {
        return $this->buildUrl($this->sanitizePath($path) ?? config('storefront.default_redirect_path'));
    }

    public function toFailureUrl(string $reason): string
    {
        $base = $this->buildUrl(config('storefront.auth_error_redirect_path'));

        return $base.(str_contains($base, '?') ? '&' : '?').'auth_error='.$reason;
    }

    public function sanitizePath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (! str_starts_with($path, '/')) {
            return null;
        }

        if (preg_match('/^[\/A-Za-z0-9._~!$&\'()*+,;=:@%?-]+$/', $path) !== 1) {
            return null;
        }

        return $path;
    }

    private function buildUrl(string $path): string
    {
        return rtrim(config('storefront.url'), '/').'/'.ltrim($path, '/');
    }
}
