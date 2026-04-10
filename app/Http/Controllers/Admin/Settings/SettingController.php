<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\Response;

class SettingController extends Controller
{
    public function index(): Response
    {
        $this->authorize('manage', Setting::class);

        return response('Admin settings index placeholder');
    }

    public function update(UpdateSettingsRequest $request): Response
    {
        $this->authorize('manage', Setting::class);

        return response('Admin settings update placeholder');
    }
}
