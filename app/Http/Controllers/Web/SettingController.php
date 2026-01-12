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
            'invoiceFooterText' => AppSetting::get('invoice_footer_text', '
        <p>Thank you for choosing ' . config('app.name') . '!</p>'),
            'bankName' => AppSetting::get('bank_name'),
            'bankAccountNo' => AppSetting::get('bank_account_no'),
            'bankAccountName' => AppSetting::get('bank_account_name'),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'invoice_footer_text' => 'nullable|string',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_no' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
        ]);

        AppSetting::set('app_name', $request->app_name);
        AppSetting::set('bank_name', $request->bank_name);
        AppSetting::set('bank_account_no', $request->bank_account_no);
        AppSetting::set('bank_account_name', $request->bank_account_name);

        if ($request->has('invoice_footer_text')) {
            AppSetting::set('invoice_footer_text', $request->invoice_footer_text);
        }

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
