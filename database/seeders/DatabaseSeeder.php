<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\Staff;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Jalankan Seeder data inti yang statis
        $this->call([
            //DummyMaster2025Seeder::class, //removed when you already run it first
            DailyBookedServiceOrderSeeder::class, // Daily booked service orders for staff
        ]);
        // run php artisan migrate:fresh --seed for dummy seeder 
        // then comment the DummySeptember2025Seeder and run daily booked daily for data SO booked
    }
}