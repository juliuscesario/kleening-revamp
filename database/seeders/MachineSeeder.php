<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;
use App\Models\MachineCategory;
use App\Models\Machine;

class MachineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Query areas with case-insensitive search (corrected: jadetebeK pattern)
        $jabodetabek = Area::whereRaw('LOWER(name) LIKE ?', ['%jadetabek%'])->first();
        $serang = Area::whereRaw('LOWER(name) LIKE ?', ['%serang%'])->first();
        $malang = Area::whereRaw('LOWER(name) LIKE ?', ['%malang%'])->first();

        if (!$jabodetabek) {
            throw new \Exception('Area "Jadetabek" not found. Please seed areas first.');
        }
        if (!$serang) {
            throw new \Exception('Area "Serang" not found. Please seed areas first.');
        }
        if (!$malang) {
            throw new \Exception('Area "Malang" not found. Please seed areas first.');
        }

        // === Categories ===
        $categories = [
            ['name' => 'Hydrovacuum', 'slug' => 'hydrovacuum', 'code_prefix' => 'hv', 'sort_order' => 1],
            ['name' => 'Steam', 'slug' => 'steam', 'code_prefix' => 's', 'sort_order' => 2],
            ['name' => 'Premium Wash', 'slug' => 'premium-wash', 'code_prefix' => 'pw', 'sort_order' => 3],
            ['name' => 'General Cleaning', 'slug' => 'general-cleaning', 'code_prefix' => 'gc', 'sort_order' => 4],
        ];

        $categoryMap = [];
        foreach ($categories as $cat) {
            $category = MachineCategory::firstOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
            $categoryMap[$cat['slug']] = $category;
        }

        // === Machines ===
        $machines = [];

        // Jabodetabek
        for ($i = 1; $i <= 8; $i++) {
            $machines[] = ['code' => "hv-jkt{$i}", 'category_slug' => 'hydrovacuum', 'area' => $jabodetabek];
        }
        for ($i = 1; $i <= 10; $i++) {
            $machines[] = ['code' => "s-jkt{$i}", 'category_slug' => 'steam', 'area' => $jabodetabek];
        }
        for ($i = 1; $i <= 8; $i++) {
            $machines[] = ['code' => "pw-jkt{$i}", 'category_slug' => 'premium-wash', 'area' => $jabodetabek];
        }
        for ($i = 1; $i <= 4; $i++) {
            $machines[] = ['code' => "gc-jkt{$i}", 'category_slug' => 'general-cleaning', 'area' => $jabodetabek];
        }

        // Serang
        $machines[] = ['code' => 'hv-srg1', 'category_slug' => 'hydrovacuum', 'area' => $serang];
        $machines[] = ['code' => 's-srg1', 'category_slug' => 'steam', 'area' => $serang];
        $machines[] = ['code' => 'pw-srg1', 'category_slug' => 'premium-wash', 'area' => $serang];

        // Malang
        $machines[] = ['code' => 'hv-mlg1', 'category_slug' => 'hydrovacuum', 'area' => $malang];
        $machines[] = ['code' => 's-mlg1', 'category_slug' => 'steam', 'area' => $malang];
        $machines[] = ['code' => 'pw-mlg1', 'category_slug' => 'premium-wash', 'area' => $malang];
        $machines[] = ['code' => 'pw-mlg2', 'category_slug' => 'premium-wash', 'area' => $malang];

        $machineMap = [];
        foreach ($machines as $m) {
            $machine = Machine::firstOrCreate(
                ['code' => $m['code']],
                [
                    'category_id' => $categoryMap[$m['category_slug']]->id,
                    'area_id' => $m['area']->id,
                    'status' => 'active',
                ]
            );
            $machineMap[$m['code']] = $machine;
        }

        // === Steam Pairing (bidirectional) ===
        $pairs = [
            ['hv' => 'hv-jkt1', 's' => 's-jkt1'],
            ['hv' => 'hv-jkt2', 's' => 's-jkt2'],
            ['hv' => 'hv-jkt3', 's' => 's-jkt3'],
            ['hv' => 'hv-jkt4', 's' => 's-jkt4'],
            ['hv' => 'hv-jkt5', 's' => 's-jkt5'],
            ['hv' => 'hv-jkt6', 's' => 's-jkt6'],
            ['hv' => 'hv-jkt7', 's' => 's-jkt7'],
            ['hv' => 'hv-jkt8', 's' => 's-jkt8'],
            ['hv' => 'hv-srg1', 's' => 's-srg1'],
            ['hv' => 'hv-mlg1', 's' => 's-mlg1'],
        ];

        foreach ($pairs as $pair) {
            $hv = $machineMap[$pair['hv']];
            $s = $machineMap[$pair['s']];

            $hv->update(['paired_machine_id' => $s->id]);
            $s->update(['paired_machine_id' => $hv->id]);
        }

        // s-jkt9 and s-jkt10 have no HV pair — leave paired_machine_id as null
    }
}
