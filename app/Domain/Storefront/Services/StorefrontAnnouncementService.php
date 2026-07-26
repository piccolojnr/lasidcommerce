<?php

namespace App\Domain\Storefront\Services;

use App\Models\Setting;
use Carbon\Carbon;

class StorefrontAnnouncementService
{
    private const PREFIX = 'storefront.announcement.';

    public function get(): array
    {
        $settings = Setting::query()
            ->where('group', 'storefront')
            ->where('key', 'like', self::PREFIX.'%')
            ->pluck('value', 'key');

        $defaults = config('storefront.announcement', []);
        $announcement = [];

        foreach ($defaults as $key => $default) {
            $announcement[$key] = $settings->get(self::PREFIX.$key, $default);
        }

        $startsAt = $this->parseDate($announcement['starts_at']);
        $endsAt = $this->parseDate($announcement['ends_at']);
        $now = now();

        $active = filter_var($announcement['enabled'], FILTER_VALIDATE_BOOLEAN)
            && filled($announcement['message'])
            && ($startsAt === null || $startsAt->lte($now))
            && ($endsAt === null || $endsAt->gte($now));

        return [
            'enabled' => $active,
            'message' => (string) $announcement['message'],
            'cta' => filled($announcement['cta_label']) && filled($announcement['cta_url'])
                ? [
                    'label' => (string) $announcement['cta_label'],
                    'url' => (string) $announcement['cta_url'],
                ]
                : null,
            'variant' => in_array($announcement['variant'], ['default', 'success', 'sale', 'warning'], true)
                ? $announcement['variant']
                : 'default',
            'starts_at' => $startsAt?->toIso8601String(),
            'ends_at' => $endsAt?->toIso8601String(),
        ];
    }

    public function getForAdmin(): array
    {
        $settings = Setting::query()
            ->where('group', 'storefront')
            ->where('key', 'like', self::PREFIX.'%')
            ->pluck('value', 'key');

        $defaults = config('storefront.announcement', []);

        $result = collect($defaults)
            ->mapWithKeys(fn ($default, $key) => [$key => $settings->get(self::PREFIX.$key, $default)])
            ->all();

        $result['enabled'] = filter_var($result['enabled'], FILTER_VALIDATE_BOOLEAN);

        return $result;
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! filled($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
