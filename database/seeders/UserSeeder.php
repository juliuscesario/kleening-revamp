<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Owner',
            'phone_number' => '081122334455',
            'password' => Hash::make('12345678'),
            'role' => 'owner',
            'area_id' => null,
        ]);

        User::create([
            'name' => 'Admin Test',
            'phone_number' => '081122334466',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'area_id' => null,
        ]);

        User::create([
            'name' => 'Staff Test',
            'phone_number' => '081122334477',
            'password' => Hash::make('12345678'),
            'role' => 'staff',
            'area_id' => null,
        ]);
    }
}
