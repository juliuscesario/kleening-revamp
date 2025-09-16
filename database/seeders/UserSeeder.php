<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Owner Utama (Tidak terikat Area)
        User::create([
            'name' => 'Owner Kleening',
            'phone_number' => '081000000001',
            'password' => Hash::make('password'), // Ganti 'password' dengan password aman
            'role' => 'owner',
            'area_id' => null,
        ]);

        // 2. Admin (Tidak terikat Area)
        User::create([
            'name' => 'Admin Pusat',
            'phone_number' => '081000000002',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'area_id' => null,
        ]);

        // 3. Co-Owner Cabang Banten (area_id = 2, sesuaikan jika perlu)
        User::create([
            'name' => 'Co-Owner Banten',
            'phone_number' => '081000000003',
            'password' => Hash::make('password'),
            'role' => 'co_owner',
            'area_id' => 2, // ID ini harus sesuai dengan ID area 'Banten' di tabel areas
        ]);
    }
}