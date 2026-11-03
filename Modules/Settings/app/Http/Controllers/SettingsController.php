<?php

namespace Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Modules\Settings\Http\Requests\UpdateSettingsRequest;
use Modules\Settings\Services\SettingService;

class SettingsController extends Controller
{
    public function __construct(private readonly SettingService $settingService) {}

    public function edit(): View
    {
        return view('settings::edit', [
            'title' => 'Settings',
            'settings' => $this->settingService->all(),
        ]);
    }

    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $settings = $this->settingService->update($request->validated());

        AuditLogger::log('settings.updated', null, $settings);

        return ApiResponse::success('Settings updated successfully.', [
            'settings' => $settings,
        ]);
    }
}
