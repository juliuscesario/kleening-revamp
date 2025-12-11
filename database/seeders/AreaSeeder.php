<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        Area::updateOrCreate(['name' => 'Jakarta']);
        Area::updateOrCreate(['name' => 'Banten']);
        Area::updateOrCreate(['name' => 'Bekasi']);
    }
}
