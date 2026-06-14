<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(protected SettingService $settings) {}

    public function index(): View
    {
        return view('admin.settings.index', [
            'settings' => $this->settings->all(),
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $this->settings->save($request->validated());

        return redirect()->route('admin.settings.index')->with('status', 'Settings saved.');
    }
}
