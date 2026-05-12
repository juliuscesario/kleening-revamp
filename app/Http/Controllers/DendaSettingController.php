<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;

class DendaSettingController extends Controller
{
    private const DENDA_KEYS = [
        'denda_telat_amount',
        'denda_telat_threshold',
        'denda_before_photo_amount',
        'denda_after_photo_amount',
        'denda_mesin_pergi_amount',
        'denda_mesin_pulang_amount',
    ];

    public function index()
    {
        $settings = [];
        foreach (self::DENDA_KEYS as $key) {
            $settings[$key] = (int) AppSetting::get($key, 0);
        }

        return view('pages.system.denda-setting', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'denda_telat_amount' => 'required|numeric|min:0',
            'denda_telat_threshold' => 'required|numeric|min:0',
            'denda_before_photo_amount' => 'required|numeric|min:0',
            'denda_after_photo_amount' => 'required|numeric|min:0',
            'denda_mesin_pergi_amount' => 'required|numeric|min:0',
            'denda_mesin_pulang_amount' => 'required|numeric|min:0',
        ]);

        foreach (self::DENDA_KEYS as $key) {
            AppSetting::set($key, (string) $validated[$key]);
        }

        return redirect()->route('system.denda-setting.index')
            ->with('success', 'Denda settings berhasil diperbarui');
    }
}
