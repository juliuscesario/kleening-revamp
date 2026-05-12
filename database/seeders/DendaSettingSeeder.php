<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class DendaSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'denda_telat_amount', 'value' => '10'],
            ['key' => 'denda_telat_threshold', 'value' => '15'],
            ['key' => 'denda_before_photo_amount', 'value' => '10'],
            ['key' => 'denda_after_photo_amount', 'value' => '10'],
            ['key' => 'denda_mesin_pergi_amount', 'value' => '10'],
            ['key' => 'denda_mesin_pulang_amount', 'value' => '10'],
        ];

        foreach ($settings as $setting) {
            AppSetting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}
