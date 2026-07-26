<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Domain\Storefront\Services\StorefrontAnnouncementService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class SettingController extends Controller
{
    public function index(StorefrontAnnouncementService $announcementService): InertiaResponse
    {
        $this->authorize('manage', Setting::class);

        return Inertia::render('admin/settings/index', [
            'catalog_settings' => [
                'new_arrival_window_days' => (int) (
                    Setting::query()->where('key', 'catalog.new_arrival_window_days')->value('value')
                    ?? config('catalog.new_arrival_window_days', 30)
                ),
            ],
            'announcement_settings' => $announcementService->getForAdmin(),
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $this->authorize('manage', Setting::class);

        foreach ($request->validated('settings') as $setting) {
            Setting::query()->updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'] ?? null,
                    'type' => $setting['type'] ?? null,
                    'group' => $setting['group'] ?? null,
                ],
            );
        }

        return back()->with('success', 'Settings updated.');
    }
}
