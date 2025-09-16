<?php

namespace Database\Seeders;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Panggil AreaSeeder dulu karena UserSeeder butuh area_id
        $this->call([
            AreaSeeder::class,
            UserSeeder::class,
        ]);
    }
}