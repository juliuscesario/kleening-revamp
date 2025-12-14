<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceOrder;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ServiceCategory;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Address;
use App\Models\Staff;
use App\Models\Area;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class DummyMaster2025Seeder extends Seeder
{
    private function readCsv($filename): array
    {
        $path = database_path('old_database/' . $filename);
        if (!file_exists($path)) {
            $this->command->error("CSV file not found: {$filename}");
            return [];
        }

        $file = fopen($path, 'r');
        $header = fgetcsv($file);
        $data = [];
        while (($row = fgetcsv($file)) !== false) {
            $data[] = array_combine($header, $row);
        }
        fclose($file);
        return $data;
    }

    private function convertToTitleCase($string): string
    {
        return ucwords(strtolower($string));
    }

    public function run(): void
    {
        $this->command->info('--- Starting Structured Dummy Data Generation ---');

        // Clean up previous transactional data
        $this->command->warn('Clearing previous transactional data...');
        DB::statement('TRUNCATE TABLE payments, invoices, service_order_items, service_order_staff, service_orders RESTART IDENTITY CASCADE');
        DB::statement('TRUNCATE TABLE areas, service_categories, services, customers, addresses, users, staff RESTART IDENTITY CASCADE');

        // --- Master Data from CSV files ---
        $areasData = $this->readCsv('Migration Kleening - Area.csv');
        $serviceCategoriesData = $this->readCsv('Migration Kleening - Service Categories.csv');
        $servicesData = $this->readCsv('Migration Kleening - Service.csv');
        $customerAddressData = $this->readCsv('Migration Kleening - Customer.csv');
        $staffUserData = $this->readCsv('Migration Kleening - Staff.csv');

        // --- Step 1: Create Prerequisite Data Sequentially ---
        $this->command->info('1. Seeding Core Data (Areas, Categories, Services, Staff, Customers)...');

        // Seed Areas
        $areaMap = [];
        foreach ($areasData as $areaDatum) {
            $area = Area::create(['name' => $areaDatum['Name']]);
            $areaMap[$area->name] = $area->id;
        }

        // Seed Service Categories and Services
        $serviceCategoryMap = [];
        foreach ($serviceCategoriesData as $categoryDatum) {
            $category = ServiceCategory::create(['name' => $categoryDatum['Categories Name']]);
            $serviceCategoryMap[$category->name] = $category->id;
        }

        foreach ($servicesData as $serviceDatum) {
            $categoryName = $serviceDatum['Category'];
            if (isset($serviceCategoryMap[$categoryName])) {
                Service::create([
                    'name' => $serviceDatum['Service Name'],
                    'category_id' => $serviceCategoryMap[$categoryName],
                    'price' => (float) $serviceDatum['Price'],
                    'cost' => (float) $serviceDatum['Cost'],
                    'description' => $serviceDatum['Description'],
                ]);
            } else {
                $this->command->warn("Service category '{$categoryName}' not found for service '{$serviceDatum['Service Name']}'. Skipping.");
            }
        }

        // Seed Customers and Addresses
        foreach ($customerAddressData as $customerAddressDatum) {
            $customerName = $customerAddressDatum['Customer Name'];
            // $camelCaseName = $this->convertToTitleCase($customerName);

            // --- CHANGED HERE: Use firstOrCreate to prevent Unique Violation ---
            // The first array is the "Search" criteria (Unique Key)
            // The second array is the data to insert if not found
            $customer = Customer::firstOrCreate(
                ['phone_number' => $customerAddressDatum['Customer Phone Number']],
                ['name' => $customerName]
            );

            $areaId = null;
            if (!empty($customerAddressDatum['Area']) && isset($areaMap[$customerAddressDatum['Area']])) {
                $areaId = $areaMap[$customerAddressDatum['Area']];
            } else {
                // Attempt to infer area from Google Maps Link if 'Area' column is empty
                $googleMapsLink = $customerAddressDatum['Google Maps Link'];
                if (str_contains($googleMapsLink, 'Jadetabek')) {
                    $areaId = $areaMap['Jadetabek'] ?? null;
                } elseif (str_contains($googleMapsLink, 'Serang')) {
                    $areaId = $areaMap['Serang'] ?? null;
                } elseif (str_contains($googleMapsLink, 'Malang')) {
                    $areaId = $areaMap['Malang'] ?? null;
                }
            }

            // Note: This creates an address for the customer.
            // If the customer was skipped (already existed), we still add the address here.
            // If you want to avoid duplicate addresses too, consider using firstOrCreate here as well.
            Address::create([
                'customer_id' => $customer->id,
                'label' => 'Rumah',
                'contact_name' => $customerAddressDatum['Contact Name'],
                'contact_phone' => $customerAddressDatum['Contact Phone'],
                'full_address' => $customerAddressDatum['Full Address'],
                'google_maps_link' => $customerAddressDatum['Google Maps Link'],
                'area_id' => $areaId,
            ]);
        }

        // Seed Staff and Users
        foreach ($staffUserData as $staffUserDatum) {
            $areaId = $areaMap[$staffUserDatum['Area']] ?? null;

            $user = User::create([
                'name' => $staffUserDatum['UserID'],
                'phone_number' => $staffUserDatum['Phone Number'],
                'password' => Hash::make($staffUserDatum['Password']),
                'role' => Str::lower(str_replace(' ', '_', $staffUserDatum['Role'])),
                'area_id' => $areaId,
            ]);

            Staff::create([
                'user_id' => $user->id,
                'name' => $staffUserDatum['Name'],
                'phone_number' => $staffUserDatum['Phone Number'],
                'area_id' => $areaId,
            ]);
        }
        
        $this->command->info('--- Structured Dummy Data Generation Completed ---');

        // --- NEW: Data Summary Report ---
        $this->command->info('');
        $this->command->info('Summary of Data Inserted:');
        
        $headers = ['Table / Model', 'Total Records'];

        $rows = [
            ['Areas', Area::count()],
            ['Service Categories', ServiceCategory::count()],
            ['Services', Service::count()],
            ['Customers', Customer::count()], // This shows unique customers (duplicates merged)
            ['Addresses', Address::count()],
            ['Users', User::count()],
            ['Staff', Staff::count()],
        ];

        $this->command->table($headers, $rows);
        $this->command->info('');
    }
}
