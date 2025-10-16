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
use Illuminate\Support\Facades\Hash; // Added
use App\Models\User; // Added

class DummyMaster2025Seeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('--- Starting Structured Dummy Data Generation ---');

        // Clean up previous transactional data
        $this->command->warn('Clearing previous transactional data...');
        DB::statement('TRUNCATE TABLE payments, invoices, service_order_items, service_order_staff, service_orders RESTART IDENTITY CASCADE');
        DB::statement('TRUNCATE TABLE areas, service_categories, services, customers, addresses, users, staff RESTART IDENTITY CASCADE');

        // --- Master Data from provided JSON ---
        $areasData = [
            ["Name" => "Jadetabek"],
            ["Name" => "Serang"],
            ["Name" => "Malang"]
        ];

        $serviceCategoriesData = [
            ["Categories Name" => "Hydrovacuum"],
            ["Categories Name" => "Premium Wash"],
            ["Categories Name" => "Car Interior Detailing"],
            ["Categories Name" => "General Cleaning"],
            ["Categories Name" => "Package"],
            ["Categories Name" => "Poles Lantai"],
            ["Categories Name" => "Add On"]
        ];

        $servicesData = [
            [
                "Service Name" => "HV Single Bed",
                "Category" => "HydroVacuum",
                "Price" => 70000,
                "Cost" => 17500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Queen Bed",
                "Category" => "HydroVacuum",
                "Price" => 80000,
                "Cost" => 20000,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV King Bed",
                "Category" => "HydroVacuum",
                "Price" => 90000,
                "Cost" => 22500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Super King Bed",
                "Category" => "HydroVacuum",
                "Price" => 100000,
                "Cost" => 25000,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Sisi Balik Single Bed",
                "Category" => "HydroVacuum",
                "Price" => 30000,
                "Cost" => 7500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Sisi Balik Queen Bed",
                "Category" => "HydroVacuum",
                "Price" => 40000,
                "Cost" => 10000,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Sisi Balik King Bed",
                "Category" => "HydroVacuum",
                "Price" => 50000,
                "Cost" => 12500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Sisi Balik Super King Bed",
                "Category" => "HydroVacuum",
                "Price" => 60000,
                "Cost" => 15000,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Divan Full Single Bed",
                "Category" => "HydroVacuum",
                "Price" => 60000,
                "Cost" => 15000,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Divan Full Queen Bed",
                "Category" => "HydroVacuum",
                "Price" => 70000,
                "Cost" => 17500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Divan Full King Bed",
                "Category" => "HydroVacuum",
                "Price" => 80000,
                "Cost" => 20000,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Divan Full Super King Bed",
                "Category" => "HydroVacuum",
                "Price" => 90000,
                "Cost" => 22500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Divan Full Kombinasi Single Bed",
                "Category" => "HydroVacuum",
                "Price" => 30000,
                "Cost" => 7500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Divan Full Kombinasi Queen Bed",
                "Category" => "HydroVacuum",
                "Price" => 40000,
                "Cost" => 10000,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Divan Full Kombinasi King Bed",
                "Category" => "HydroVacuum",
                "Price" => 50000,
                "Cost" => 12500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Divan Full Kombinasi Super King Bed",
                "Category" => "HydroVacuum",
                "Price" => 60000,
                "Cost" => 15000,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Headboard Single Bed",
                "Category" => "HydroVacuum",
                "Price" => 30000,
                "Cost" => 7500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Headboard Queen Bed",
                "Category" => "HydroVacuum",
                "Price" => 40000,
                "Cost" => 10000,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Headboard King Bed",
                "Category" => "HydroVacuum",
                "Price" => 50000,
                "Cost" => 12500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Headboard Super King Bed",
                "Category" => "HydroVacuum",
                "Price" => 60000,
                "Cost" => 15000,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Matras Tebal/Lipat Single",
                "Category" => "HydroVacuum",
                "Price" => 50000,
                "Cost" => 12500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Matras Tebal/Lipat Queen",
                "Category" => "HydroVacuum",
                "Price" => 60000,
                "Cost" => 15000,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Matras Tipis",
                "Category" => "HydroVacuum",
                "Price" => 30000,
                "Cost" => 7500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Bantal / Guling",
                "Category" => "HydroVacuum",
                "Price" => 10000,
                "Cost" => 2500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Sofa Single Standard",
                "Category" => "HydroVacuum",
                "Price" => 40000,
                "Cost" => 10000,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Sofa Single Jumbo",
                "Category" => "HydroVacuum",
                "Price" => 50000,
                "Cost" => 12500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Sofa Panjang / Seat Standard",
                "Category" => "HydroVacuum",
                "Price" => 35000,
                "Cost" => 8750,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Sofa Panjang / Seat Jumbo",
                "Category" => "HydroVacuum",
                "Price" => 40000,
                "Cost" => 10000,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Sofa Bed Standard",
                "Category" => "HydroVacuum",
                "Price" => 80000,
                "Cost" => 20000,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Sofa Bed Jumbo",
                "Category" => "HydroVacuum",
                "Price" => 90000,
                "Cost" => 22500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Sofa Mini Single Standard",
                "Category" => "HydroVacuum",
                "Price" => 25000,
                "Cost" => 6250,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Sofa Mini Single Jumbo",
                "Category" => "HydroVacuum",
                "Price" => 30000,
                "Cost" => 7500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Bantalan Sofa Standard",
                "Category" => "HydroVacuum",
                "Price" => 10000,
                "Cost" => 2500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Bantalan Sofa Jumbo",
                "Category" => "HydroVacuum",
                "Price" => 15000,
                "Cost" => 3750,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Bantal Sofa S ",
                "Category" => "HydroVacuum",
                "Price" => 5000,
                "Cost" => 1250,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Bantal Sofa L",
                "Category" => "HydroVacuum",
                "Price" => 10000,
                "Cost" => 2500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Kursi Makan Standard",
                "Category" => "HydroVacuum",
                "Price" => 20000,
                "Cost" => 5000,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Kursi Makan Jumbo",
                "Category" => "HydroVacuum",
                "Price" => 20000,
                "Cost" => 5000,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Kursi Kantor Standard",
                "Category" => "HydroVacuum",
                "Price" => 30000,
                "Cost" => 7500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Kursi Kantor Jumbo",
                "Category" => "HydroVacuum",
                "Price" => 30000,
                "Cost" => 7500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Carpet <1cm Standard <20m2",
                "Category" => "HydroVacuum",
                "Price" => 20000,
                "Cost" => 5000,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Carpet <1cm Standard >20m2",
                "Category" => "HydroVacuum",
                "Price" => 15000,
                "Cost" => 3750,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Carpet >1cm Bulu <20m2",
                "Category" => "HydroVacuum",
                "Price" => 30000,
                "Cost" => 7500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Carpet >1cm Bulu >20m2",
                "Category" => "HydroVacuum",
                "Price" => 25000,
                "Cost" => 6250,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Gorden /M2 <50m2",
                "Category" => "HydroVacuum",
                "Price" => 5000,
                "Cost" => 1250,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Gorden /M2 >50m2",
                "Category" => "HydroVacuum",
                "Price" => 4000,
                "Cost" => 1000,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Vitrace/M2 <50m2",
                "Category" => "HydroVacuum",
                "Price" => 4000,
                "Cost" => 1000,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Vitrace /M2 >50m2",
                "Category" => "HydroVacuum",
                "Price" => 3000,
                "Cost" => 750,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Matras Small Newborn <1m",
                "Category" => "HydroVacuum",
                "Price" => 30000,
                "Cost" => 7500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Matras Medium >1m",
                "Category" => "HydroVacuum",
                "Price" => 40000,
                "Cost" => 10000,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Baby Box / Crib",
                "Category" => "HydroVacuum",
                "Price" => 50000,
                "Cost" => 12500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Baby Box Full",
                "Category" => "HydroVacuum",
                "Price" => 90000,
                "Cost" => 22500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Bantal/boneka baby",
                "Category" => "HydroVacuum",
                "Price" => 5000,
                "Cost" => 1250,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Playmat Gulung/lipat",
                "Category" => "HydroVacuum",
                "Price" => 30000,
                "Cost" => 7500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Playmat Medium Kulit <6 pad",
                "Category" => "HydroVacuum",
                "Price" => 50000,
                "Cost" => 12500,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Playmat Large Kulit >6 pad",
                "Category" => "HydroVacuum",
                "Price" => 75000,
                "Cost" => 18750,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Bouncer",
                "Category" => "HydroVacuum",
                "Price" => 25000,
                "Cost" => 6250,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Stroller",
                "Category" => "HydroVacuum",
                "Price" => 25000,
                "Cost" => 6250,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Carseat",
                "Category" => "HydroVacuum",
                "Price" => 25000,
                "Cost" => 6250,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "HV Hipseat / Baby Carrier",
                "Category" => "HydroVacuum",
                "Price" => 15000,
                "Cost" => 3750,
                "Description" => "Hydrovacuum"
            ],
            [
                "Service Name" => "PW Single Bed",
                "Category" => "Premium Wash",
                "Price" => 260000,
                "Cost" => 91000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Queen Bed",
                "Category" => "Premium Wash",
                "Price" => 340000,
                "Cost" => 119000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW King Bed",
                "Category" => "Premium Wash",
                "Price" => 380000,
                "Cost" => 133000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Super King Bed",
                "Category" => "Premium Wash",
                "Price" => 420000,
                "Cost" => 147000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Sisi Balik Single Bed",
                "Category" => "Premium Wash",
                "Price" => 130000,
                "Cost" => 45500,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Sisi Balik Queen Bed",
                "Category" => "Premium Wash",
                "Price" => 170000,
                "Cost" => 59500,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Sisi Balik King Bed",
                "Category" => "Premium Wash",
                "Price" => 190000,
                "Cost" => 66500,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Sisi Balik Super King Bed",
                "Category" => "Premium Wash",
                "Price" => 210000,
                "Cost" => 73500,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Divan Full Single Bed",
                "Category" => "Premium Wash",
                "Price" => 260000,
                "Cost" => 91000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Divan Full Queen Bed",
                "Category" => "Premium Wash",
                "Price" => 340000,
                "Cost" => 119000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Divan Full King Bed",
                "Category" => "Premium Wash",
                "Price" => 380000,
                "Cost" => 133000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Divan Full Super King Bed",
                "Category" => "Premium Wash",
                "Price" => 420000,
                "Cost" => 147000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Divan Kombinasi Single Bed",
                "Category" => "Premium Wash",
                "Price" => 130000,
                "Cost" => 45500,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Divan Kombinasi Queen Bed",
                "Category" => "Premium Wash",
                "Price" => 170000,
                "Cost" => 59500,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Divan Kombinasi King Bed",
                "Category" => "Premium Wash",
                "Price" => 190000,
                "Cost" => 66500,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Divan Kombinasi Super King Bed",
                "Category" => "Premium Wash",
                "Price" => 210000,
                "Cost" => 73500,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Headboard Single Bed",
                "Category" => "Premium Wash",
                "Price" => 100000,
                "Cost" => 35000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Headboard Queen Bed",
                "Category" => "Premium Wash",
                "Price" => 140000,
                "Cost" => 49000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Headboard King Bed",
                "Category" => "Premium Wash",
                "Price" => 160000,
                "Cost" => 56000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Headboard Super King Bed",
                "Category" => "Premium Wash",
                "Price" => 180000,
                "Cost" => 63000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Matras Tebal/Lipat Single",
                "Category" => "Premium Wash",
                "Price" => 100000,
                "Cost" => 35000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Matras Tebal/Lipat Queen",
                "Category" => "Premium Wash",
                "Price" => 140000,
                "Cost" => 49000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Matras Tebal/Lipat King",
                "Category" => "Premium Wash",
                "Price" => 160000,
                "Cost" => 56000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Matras Tebal/Lipat Super King",
                "Category" => "Premium Wash",
                "Price" => 180000,
                "Cost" => 63000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Matras Tipis",
                "Category" => "Premium Wash",
                "Price" => 100000,
                "Cost" => 35000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Bantal / Guling",
                "Category" => "Premium Wash",
                "Price" => 30000,
                "Cost" => 10500,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Sofa Single Standard",
                "Category" => "Premium Wash",
                "Price" => 95000,
                "Cost" => 33250,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Sofa Single Jumbo",
                "Category" => "Premium Wash",
                "Price" => 100000,
                "Cost" => 35000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Sofa Panjang / Seat Standard",
                "Category" => "Premium Wash",
                "Price" => 90000,
                "Cost" => 31500,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Sofa Panjang / Seat Jumbo",
                "Category" => "Premium Wash",
                "Price" => 95000,
                "Cost" => 33250,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Sofa Bed Standard",
                "Category" => "Premium Wash",
                "Price" => 260000,
                "Cost" => 91000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Sofa Bed Jumbo",
                "Category" => "Premium Wash",
                "Price" => 300000,
                "Cost" => 105000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Sofa Mini Single Standard",
                "Category" => "Premium Wash",
                "Price" => 60000,
                "Cost" => 21000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Sofa Mini Single Jumbo",
                "Category" => "Premium Wash",
                "Price" => 70000,
                "Cost" => 24500,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Bantalan Sofa Standard",
                "Category" => "Premium Wash",
                "Price" => 30000,
                "Cost" => 10500,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Bantalan Sofa Jumbo",
                "Category" => "Premium Wash",
                "Price" => 50000,
                "Cost" => 17500,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Bantal Sofa S",
                "Category" => "Premium Wash",
                "Price" => 20000,
                "Cost" => 7000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Bantal Sofa M",
                "Category" => null,
                "Price" => 30000,
                "Cost" => 10500,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Bantal Sofa L",
                "Category" => "Premium Wash",
                "Price" => 50000,
                "Cost" => 17500,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Kursi Makan Standard",
                "Category" => "Premium Wash",
                "Price" => 40000,
                "Cost" => 14000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Kursi Makan Jumbo",
                "Category" => "Premium Wash",
                "Price" => 50000,
                "Cost" => 17500,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Kursi Kantor Standard",
                "Category" => "Premium Wash",
                "Price" => 50000,
                "Cost" => 17500,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Kursi Kantor Jumbo",
                "Category" => "Premium Wash",
                "Price" => 60000,
                "Cost" => 21000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Carpet <1cm Standard <20m2",
                "Category" => "Premium Wash",
                "Price" => 60000,
                "Cost" => 21000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Carpet <1cm Standard >20m2",
                "Category" => "Premium Wash",
                "Price" => 50000,
                "Cost" => 17500,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Carpet >1cm Bulu <20m2",
                "Category" => "Premium Wash",
                "Price" => 90000,
                "Cost" => 31500,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Carpet >1cm Bulu >20m2",
                "Category" => "Premium Wash",
                "Price" => 80000,
                "Cost" => 28000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Gorden /M2 <50m2",
                "Category" => "Premium Wash",
                "Price" => 15000,
                "Cost" => 5250,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Gorden /M2 >50m2",
                "Category" => "Premium Wash",
                "Price" => 12000,
                "Cost" => 4200,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Vitrace/M2 <50m2",
                "Category" => "Premium Wash",
                "Price" => 12000,
                "Cost" => 4200,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Vitrace /M2 >50m2",
                "Category" => "Premium Wash",
                "Price" => 8000,
                "Cost" => 2800,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Matras Small Newborn <1m",
                "Category" => "Premium Wash",
                "Price" => 100000,
                "Cost" => 35000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Matras Medium >1m",
                "Category" => "Premium Wash",
                "Price" => 200000,
                "Cost" => 70000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Baby Box / Crib",
                "Category" => "Premium Wash",
                "Price" => 150000,
                "Cost" => 52500,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Baby Box Full",
                "Category" => "Premium Wash",
                "Price" => 350000,
                "Cost" => 122500,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Bantal/boneka baby",
                "Category" => "Premium Wash",
                "Price" => 25000,
                "Cost" => 8750,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Playmat Gulung/lipat",
                "Category" => "Premium Wash",
                "Price" => 100000,
                "Cost" => 35000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Playmat Medium Kulit <6 pad",
                "Category" => "Premium Wash",
                "Price" => 200000,
                "Cost" => 70000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Playmat Large Kulit >6 pad",
                "Category" => "Premium Wash",
                "Price" => 300000,
                "Cost" => 105000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Bouncer",
                "Category" => "Premium Wash",
                "Price" => 150000,
                "Cost" => 52500,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Stroller",
                "Category" => "Premium Wash",
                "Price" => 250000,
                "Cost" => 87500,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Carseat",
                "Category" => "Premium Wash",
                "Price" => 250000,
                "Cost" => 87500,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "PW Hipseat / Baby Carrier",
                "Category" => "Premium Wash",
                "Price" => 100000,
                "Cost" => 35000,
                "Description" => "Premium Wash"
            ],
            [
                "Service Name" => "CID Small",
                "Category" => "Car Interior Detailing",
                "Price" => 500000,
                "Cost" => 175000,
                "Description" => "Car Interior Detailing"
            ],
            [
                "Service Name" => "CID Sedan",
                "Category" => "Car Interior Detailing",
                "Price" => 700000,
                "Cost" => 245000,
                "Description" => "Car Interior Detailing"
            ],
            [
                "Service Name" => "CID Medium",
                "Category" => "Car Interior Detailing",
                "Price" => 700000,
                "Cost" => 245000,
                "Description" => "Car Interior Detailing"
            ],
            [
                "Service Name" => "CID Large",
                "Category" => "Car Interior Detailing",
                "Price" => 850000,
                "Cost" => 297500,
                "Description" => "Car Interior Detailing"
            ],
            [
                "Service Name" => "CID Luxury",
                "Category" => "Car Interior Detailing",
                "Price" => 950000,
                "Cost" => 332500,
                "Description" => "Car Interior Detailing"
            ],
            [
                "Service Name" => "General Cleaning",
                "Category" => "General Cleaning",
                "Price" => 85000,
                "Cost" => 42500,
                "Description" => "General Cleaning"
            ],
            [
                "Service Name" => "Deep Cleaning",
                "Category" => "General Cleaning",
                "Price" => 20000,
                "Cost" => 10000,
                "Description" => "General Cleaning"
            ],
            [
                "Service Name" => "Deep Cleaning WC /m2",
                "Category" => "General Cleaning",
                "Price" => 100000,
                "Cost" => 50000,
                "Description" => "General Cleaning"
            ],
            [
                "Service Name" => "Deep Cleaning Kaca /m2",
                "Category" => "General Cleaning",
                "Price" => 100000,
                "Cost" => 50000,
                "Description" => "General Cleaning"
            ],
            [
                "Service Name" => "Paket Kosan",
                "Category" => "Package",
                "Price" => 175000,
                "Cost" => 61250,
                "Description" => "Package"
            ],
            [
                "Service Name" => "Paket Studio",
                "Category" => "Package",
                "Price" => 200000,
                "Cost" => 70000,
                "Description" => "Package"
            ],
            [
                "Service Name" => "Paket Family",
                "Category" => "Package",
                "Price" => 400000,
                "Cost" => 140000,
                "Description" => "Package"
            ],
            [
                "Service Name" => "Paket Big Home",
                "Category" => "Package",
                "Price" => 590000,
                "Cost" => 206500,
                "Description" => "Package"
            ],
            [
                "Service Name" => "Poles Marmer /m2",
                "Category" => "Poles Lantai",
                "Price" => 40000,
                "Cost" => 12000,
                "Description" => "Poles"
            ],
            [
                "Service Name" => "Poles Granit /m2",
                "Category" => "Poles Lantai",
                "Price" => 50000,
                "Cost" => 15000,
                "Description" => "Poles"
            ],
            [
                "Service Name" => "Poles keramik /m2",
                "Category" => "Poles Lantai",
                "Price" => 20000,
                "Cost" => 6000,
                "Description" => "Poles"
            ],
            [
                "Service Name" => "Cairan Kutu Kasur",
                "Category" => "Add On",
                "Price" => 80000,
                "Cost" => 40000,
                "Description" => "Cairan Kutu"
            ],
            [
                "Service Name" => "Fogging Disinfectant /m2",
                "Category" => "Add On",
                "Price" => 4000,
                "Cost" => 1200,
                "Description" => "Fogging"
            ],
            [
                "Service Name" => "Kipas Blower",
                "Category" => "Add On",
                "Price" => 100000,
                "Cost" => 10000,
                "Description" => "Blower Pengering"
            ],
            [
                "Service Name" => "Free Kipas Blower",
                "Category" => "Add On",
                "Price" => 0,
                "Cost" => 0,
                "Description" => "Blower Pengering"
            ],
            [
                "Service Name" => "Free Transport",
                "Category" => "Add On",
                "Price" => 0,
                "Cost" => 0,
                "Description" => "Transport"
            ],
            [
                "Service Name" => "Transport Standard",
                "Category" => "Add On",
                "Price" => 50000,
                "Cost" => 50000,
                "Description" => "Transport"
            ],
            [
                "Service Name" => "Transport Sedang",
                "Category" => "Add On",
                "Price" => 100000,
                "Cost" => 100000,
                "Description" => "Transport"
            ],
            [
                "Service Name" => "Transport Jauh",
                "Category" => "Add On",
                "Price" => 150000,
                "Cost" => 150000,
                "Description" => "Transport"
            ],
            [
                "Service Name" => "Ongkos Kirim",
                "Category" => "Add On",
                "Price" => 100,
                "Cost" => 100,
                "Description" => "Ongkos Kirim"
            ],
            [
                "Service Name" => "Biaya Lembur Malam",
                "Category" => "Add On",
                "Price" => 50000,
                "Cost" => 50000,
                "Description" => "Lemburan"
            ],
            [
                "Service Name" => "Biaya Cancel",
                "Category" => "Add On",
                "Price" => 100000,
                "Cost" => 100000,
                "Description" => "Biaya Pembatalan"
            ]
        ];

        $customerAddressData = [
            [
                "Customer Name" => "Yaman Pakola - Kb Jeruk",
                "Customer Phone Number" => "6281385569127",
                "Address Label" => "Rumah",
                "Contact Name" => "Yaman Pakola",
                "Contact Phone" => "6281385569127",
                "Full Address" => "Jalan Daud No. 2, Kebon Jeruk, Jakarta Barat",
                "Google Maps Link" => "https://maps.app.goo.gl/rNLoxGqaV7uZ6Eyu6,Jadetabek",
                "Area" => "Jadetabek"
            ],
            [
                "Customer Name" => "Oka-kbjeruk",
                "Customer Phone Number" => "6282121289992",
                "Address Label" => "Rumah",
                "Contact Name" => "Oka-kbjeruk",
                "Contact Phone" => "6282121289992",
                "Full Address" => "l. Palem 1 No 20 RT 08/RW 07 Duri Kepa, Kebon Jeruk, Jakarta Barat",
                "Google Maps Link" => "https://www.google.com/maps?q=-6.182535171508789+106.77485656738281,Jadetabek",
                "Area" => "Jadetabek"
            ],
            [
                "Customer Name" => "Chinika - Cipondoh",
                "Customer Phone Number" => "6287888669633",
                "Address Label" => "Rumah",
                "Contact Name" => "Chinika - Cipondoh",
                "Contact Phone" => "6287888669633",
                "Full Address" => "Perumahan Grand Poris Blok AA1 No.1, Cipondoh Indah, Cipondoh, Tangerang 15148 note : Rumah bewarna biru, bersebelahan dengan Indomaret",
                "Google Maps Link" => "https://www.google.com/maps?q=-6.174056529998779+106.68579864501953,Jadetabek",
                "Area" => "Jadetabek"
            ],
            [
                "Customer Name" => "Nining Awaliyah - Walantaka serang",
                "Customer Phone Number" => "6282249054571",
                "Address Label" => "Rumah",
                "Contact Name" => "Nining Awaliyah - Walantaka serang",
                "Contact Phone" => "6282249054571",
                "Full Address" => "Persada Banten blok E6 no 39 (Masuk persada setelah Alfa ada gapura blok E sebelah kanan, masuk lurus belok kiri, belok kanan, belok kanan, belok kiri, lurus gang ke 2 belok kiri, lurus mentok ada cat warna hijau)",
                "Google Maps Link" => "https://www.google.com/maps/place/6%C2%B006'55.5%22S+106%C2%B012'52.4%22E/@-6.1154173,106.2145643,17z/data=!3m1!4b1!4m4!3m3!8m2!3d-6.1154173!4d106.2145643?entry=ttu",
                "Area" => "Serang"
            ],
            [
                "Customer Name" => "Rudy - Serang",
                "Customer Phone Number" => "6281224552939",
                "Address Label" => "Sekolah",
                "Contact Name" => "Rudy - Serang",
                "Contact Phone" => "6281224552939",
                "Full Address" => "tk sansan stationary Jl kh abdul latif sblh ayam pak gembus ps rau secang serang",
                "Google Maps Link" => "https://www.google.com/maps/place/6%C2%B006'48.4%22S+106%C2%B009'59.6%22E/@-6.1134312,106.1665508,17z/data=!3m1!4b1!4m4!3m3!8m2!3d-6.1134312!4d106.1665508?entry=ttu",
                "Area" => "Serang"
            ],
            [
                "Customer Name" => "Farel - Malang",
                "Customer Phone Number" => "6281315993195",
                "Address Label" => "Rumah",
                "Contact Name" => "Farel - Malang",
                "Contact Phone" => "6281315993195",
                "Full Address" => "jl taman sulfatXI/1",
                "Google Maps Link" => "https://maps.app.goo.gl/PGb71vFFVxLuM68u5?g_st=ac,Malang",
                "Area" => "Malang"
            ],
            [
                "Customer Name" => "Juwita - Malang",
                "Customer Phone Number" => "6287739006959",
                "Address Label" => "Kantor",
                "Contact Name" => "Juwita - Malang",
                "Contact Phone" => "6287739006959",
                "Full Address" => "Jl. Selorejo no. 39, Malang",
                "Google Maps Link" => "https://maps.app.goo.gl/55DsgPNewMkhfRP37?g_st=ac,Malang",
                "Area" => "Malang"
            ]
        ];

        $staffUserData = [
            [
                "Name" => "Iwan",
                "Phone Number" => "088212734806",
                "Area" => "Jadetabek",
                "UserID" => "Iwan",
                "Password" => "123456",
                "Role" => "owner"
            ],
            [
                "Name" => "Christie",
                "Phone Number" => "085723123922",
                "Area" => "Jadetabek",
                "UserID" => "Christie",
                "Password" => "123456",
                "Role" => "owner"
            ],
            [
                "Name" => "Rinda",
                "Phone Number" => "085162624324",
                "Area" => "Jadetabek",
                "UserID" => "Rinda",
                "Password" => "123456",
                "Role" => "admin"
            ],
            [
                "Name" => "Salsa",
                "Phone Number" => "085695703059",
                "Area" => "Jadetabek",
                "UserID" => "Salsa",
                "Password" => "123456",
                "Role" => "admin"
            ],
            [
                "Name" => "Dani",
                "Phone Number" => "0882127348061", // Changed to be unique
                "Area" => "Jadetabek",
                "UserID" => "Dani",
                "Password" => "123456",
                "Role" => "staff"
            ],
            [
                "Name" => "Iyan",
                "Phone Number" => "0895363568384",
                "Area" => "Jadetabek",
                "UserID" => "Iyan",
                "Password" => "123456",
                "Role" => "staff"
            ],
            [
                "Name" => "Mei",
                "Phone Number" => "081296038802",
                "Area" => "Serang",
                "UserID" => "Mei",
                "Password" => "123456",
                "Role" => "co_owner"
            ],
            [
                "Name" => "Adhi",
                "Phone Number" => "088210213903",
                "Area" => "Serang",
                "UserID" => "Adhi",
                "Password" => "123456",
                "Role" => "staff"
            ],
            [
                "Name" => "William",
                "Phone Number" => "08990090030",
                "Area" => "Malang",
                "UserID" => "William",
                "Password" => "123456",
                "Role" => "co_owner"
            ],
            [
                "Name" => "Lutfi",
                "Phone Number" => "083862857782",
                "Area" => "Malang",
                "UserID" => "Lutfi",
                "Password" => "123456",
                "Role" => "staff"
            ]
        ];

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

            // Seed specific services from the servicesData array
            foreach ($servicesData as $serviceDatum) {
                if (($serviceDatum['Category'] ?? null) === $category->name) {
                    Service::create([
                        'name' => $serviceDatum['Service Name'],
                        'category_id' => $category->id,
                        'price' => $serviceDatum['Price'],
                        'cost' => $serviceDatum['Cost'],
                        'description' => $serviceDatum['Description'],
                    ]);
                }
            }
        }

        // Seed Customers and Addresses
        foreach ($customerAddressData as $customerAddressDatum) {
            $customer = Customer::create([
                'name' => $customerAddressDatum['Customer Name'],
                'phone_number' => $customerAddressDatum['Customer Phone Number'],
            ]);

            $areaId = null;
            if (!empty($customerAddressDatum['Area']) && isset($areaMap[$customerAddressDatum['Area']])) {
                $areaId = $areaMap[$customerAddressDatum['Area']];
            } elseif (str_contains($customerAddressDatum['Google Maps Link'], 'Jadetabek')) {
                $areaId = $areaMap['Jadetabek'] ?? null;
            } elseif (str_contains($customerAddressDatum['Google Maps Link'], 'Serang')) {
                $areaId = $areaMap['Serang'] ?? null;
            } elseif (str_contains($customerAddressDatum['Google Maps Link'], 'Malang')) {
                $areaId = $areaMap['Malang'] ?? null;
            }

            Address::create([
                'customer_id' => $customer->id,
                'label' => $customerAddressDatum['Address Label'],
                'contact_name' => $customerAddressDatum['Contact Name'],
                'contact_phone' => $customerAddressDatum['Contact Phone'],
                'full_address' => $customerAddressDatum['Full Address'],
                'google_maps_link' => $customerAddressDatum['Google Maps Link'],
                'area_id' => $areaId,
            ]);
        }

        // Seed Staff and Users
        foreach ($staffUserData as $staffUserDatum) {
            $user = User::create([
                'name' => $staffUserDatum['UserID'],
                'phone_number' => $staffUserDatum['Phone Number'], // Assuming phone_number is unique and can be used as a login identifier if needed
                'password' => Hash::make($staffUserDatum['Password']),
                'role' => $staffUserDatum['Role'],
            ]);

            $areaId = $areaMap[$staffUserDatum['Area']] ?? null;

            Staff::create([
                'user_id' => $user->id,
                'name' => $staffUserDatum['Name'],
                'phone_number' => $staffUserDatum['Phone Number'],
                'area_id' => $areaId,
            ]);
        }

    //     // --- Step 2: Fetch all data needed for the main loop ---
    //     $this->command->info('2. Fetching data for transaction generation...');
    //     $customers = Customer::all();
    //     $allStaff = Staff::withoutGlobalScope(\App\Models\Scopes\AreaScope::class)->get();
    //     $onlyStaff = $allStaff->filter(function ($s) {
    //         return $s->user->role === 'staff';
    //     });

    //     if ($customers->isEmpty()) {
    //         $this->command->error('Customer collection is empty after seeding. Cannot proceed.');
    //         return;
    //     }
    //     if ($onlyStaff->isEmpty()) {
    //         $this->command->error('No staff members found with role \'staff\'. Cannot assign service orders.');
    //         return;
    //     }

    //     // --- Step 3: Main Seeder Logic ---
    //     $this->command->info('3. Generating transactional data from Jan to Sep 2025...');
    //     $period = CarbonPeriod::create('2025-01-01', '2025-09-28');
    //     $invoiceCounter = 1;
    //     $delayedMayInvoices = [];
    //     $totalOrdersCreated = 0;
    //     $customerIndex = 0; // For cycling through customers

    //     $monthlyWeights = [ 1 => 1.0, 2 => 0.5, 3 => 1.0, 4 => 1.5, 5 => 1.0, 6 => 1.5, 7 => 1.0, 8 => 1.0, 9 => 1.0 ];
    //     $baseOrdersPerDay = 17.5; // Adjusted for 50-100 orders per customer per month

    //     foreach ($period as $date) {
    //         $monthWeight = $monthlyWeights[$date->month] ?? 1.0;
    //         $ordersPerDay = round($baseOrdersPerDay * $monthWeight * (rand(80, 120) / 100));

    //         for ($i = 0; $i < $ordersPerDay; $i++) {
    //             $totalOrdersCreated++;

    //             // Cycle through customers
    //             $customer = $customers->get($customerIndex);
    //             $customerIndex = ($customerIndex + 1) % $customers->count();

    //             $isCancelled = (rand(1, 100) <= 15); // Reduced cancellation rate to 15%
    //             $hasInvoice = (rand(1, 100) <= 70); // 70% chance of having an invoice for non-cancelled orders

    //             $serviceOrderStatus = 'booked'; // Default status

    //             if ($isCancelled) {
    //                 $serviceOrderStatus = 'cancelled';
    //             } elseif ($hasInvoice) {
    //                 $serviceOrderStatus = 'invoiced';
    //             } else {
    //                 // For non-cancelled, non-invoiced orders, randomly assign booked, proses, or done
    //                 $randStatus = rand(1, 100);
    //                 if ($randStatus <= 30) {
    //                     $serviceOrderStatus = 'booked';
    //                 } elseif ($randStatus <= 60) {
    //                     $serviceOrderStatus = 'proses';
    //                 } else {
    //                     $serviceOrderStatus = 'done'; // Done without an invoice
    //                 }
    //             }

    //             $serviceOrder = ServiceOrder::factory()->create([
    //                 'status' => $serviceOrderStatus,
    //                 'work_date' => $date,
    //                 'customer_id' => $customer->id,
    //             ]);

    //             $serviceOrder->staff()->sync($onlyStaff->random(rand(1, min(2, $onlyStaff->count())))->pluck('id')->toArray());

    //             if ($isCancelled) continue;

    //             // Only create invoice if service order status is 'invoiced'
    //             if ($serviceOrderStatus === 'invoiced') {
    //                 $subtotal = $serviceOrder->items->sum('total');
    //                 $transport_fee = rand(10000, 50000);
    //                 $invoice = Invoice::create([
    //                     'service_order_id' => $serviceOrder->id,
    //                     'invoice_number' => 'INV/' . $date->year . '/' . $invoiceCounter++,
    //                     'issue_date' => $serviceOrder->work_date->addDays(rand(1, 2)),
    //                     'due_date' => $serviceOrder->work_date->addDays(7),
    //                     'subtotal' => $subtotal,
    //                     'transport_fee' => $transport_fee,
    //                     'grand_total' => $subtotal + $transport_fee,
    //                     'status' => 'sent',
    //                 ]);

    //                 $isMay = $date->month == 5;
    //                 $shouldPay = $isMay ? (rand(1, 100) <= 30) : (rand(1, 100) <= 90);

    //                 if ($shouldPay) {
    //                     Payment::create([
    //                         'invoice_id' => $invoice->id,
    //                         'amount' => $invoice->grand_total,
    //                         'payment_date' => $date->copy()->addDays(rand(2, 6)),
    //                         'payment_method' => 'Transfer',
    //                     ]);
    //                     $invoice->update(['status' => 'paid']);
    //                 } elseif ($isMay) {
    //                     $delayedMayInvoices[] = $invoice;
    //                 }
    //             }
    //         }
    //     }

    //     // --- Step 4: Process Delayed Payments ---
    //     $this->command->info('4. Processing delayed May payments...');
    //     foreach ($delayedMayInvoices as $invoice) {
    //         Payment::create([
    //             'invoice_id' => $invoice->id,
    //             'amount' => $invoice->grand_total,
    //             'payment_date' => Carbon::create(2025, 6, rand(1, 30)),
    //             'payment_method' => 'Transfer',
    //         ]);
    //         $invoice->update(['status' => 'paid']);
    //     }

    //     $this->command->info("Total orders created: $totalOrdersCreated");
        $this->command->info('--- Structured Dummy Data Generation Completed ---');
    }
}
