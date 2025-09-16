<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        Area::create(['name' => 'Jakarta']);
        Area::create(['name' => 'Banten']);
        Area::create(['name' => 'Bekasi']);
    }
}
