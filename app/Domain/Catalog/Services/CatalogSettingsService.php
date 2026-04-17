<?php

namespace App\Domain\Catalog\Services;

use App\Models\Setting;

class CatalogSettingsService
{
    public function newArrivalWindowDays(): int
    {
        $value = Setting::query()
            ->where('key', 'catalog.new_arrival_window_days')
            ->value('value');

        if ($value === null || ! is_numeric($value)) {
            return max(1, (int) config('catalog.new_arrival_window_days', 30));
        }

        return max(1, (int) $value);
    }
}
