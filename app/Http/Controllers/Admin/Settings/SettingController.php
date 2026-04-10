<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class SettingController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('manage', Setting::class);

        return Inertia::render('admin/settings/index');
    }

    public function update(UpdateSettingsRequest $request): Response
    {
        $this->authorize('manage', Setting::class);

        return response('Admin settings update placeholder');
    }
}
