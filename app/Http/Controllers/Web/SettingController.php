<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        return view('settings.index', [
            'appName' => AppSetting::get('app_name', config('app.name')),
            'appLogo' => AppSetting::get('app_logo'),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        AppSetting::set('app_name', $request->app_name);

        if ($request->hasFile('app_logo')) {
            // Delete old logo if it exists and is not the default
            $oldLogo = AppSetting::get('app_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            $path = $request->file('app_logo')->store('custom_branding', 'public');
            AppSetting::set('app_logo', $path);
        }

        return redirect()->route('web.settings.index')->with('success', 'Settings updated successfully.');
    }
}
