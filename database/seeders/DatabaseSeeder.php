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
            AreaSeeder::class,
            UserSeeder::class, // Ini membuat Owner, Admin, Co-Owner
            DummySeptember2025Seeder::class, // Add this line
        ]);

        // 2. Gunakan Factory untuk membuat data dummy dalam jumlah banyak
        
        // Buat 5 Kategori Layanan
        ServiceCategory::factory(5)->create()->each(function ($category) {
            // Untuk setiap kategori, buat 3-5 layanan di dalamnya
            Service::factory(rand(3, 5))->create(['category_id' => $category->id]);
        });
        
        // Buat 20 Customer, dan untuk setiap customer, buat 1-2 alamat
        Customer::factory(20)->create()->each(function ($customer) {
            Address::factory(rand(2, 3))->create(['customer_id' => $customer->id]);
        });

        // Buat 10 Staff (mereka akan otomatis ditempatkan di area acak)
        Staff::factory(10)->create();

        // Buat 50 Service Order (item dan staff akan otomatis dibuat oleh Factory)
        ServiceOrder::factory(50)->create();
    }
}