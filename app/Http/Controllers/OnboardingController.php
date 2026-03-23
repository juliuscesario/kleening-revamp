<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Area;
use App\Models\Customer;
use App\Models\OnboardingStep;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Staff;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    private $steps = [
        'area' => 1,
        'staff' => 2,
        'service_category' => 3,
        'service' => 4,
        'customer' => 5,
        'settings' => 6,
        'password' => 7,
    ];

    public function index()
    {
        $user = auth()->user();
        $tenant = $user->tenant;

        if ($tenant->onboarding_completed_at) {
            return redirect()->route('dashboard', ['tenant_slug' => $tenant->slug]);
        }

        // Get current step
        $currentStep = OnboardingStep::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->orderBy('id', 'asc')
            ->first();

        if (!$currentStep) {
            // All steps likely done, but completed_at not set? 
            // Or maybe it's the first time.
            if (OnboardingStep::where('tenant_id', $tenant->id)->count() === 0) {
               $this->initializeSteps($tenant);
               return redirect()->route('onboarding.index', ['tenant_slug' => $tenant->slug]);
            }

            // Check if all are completed
            if (OnboardingStep::where('tenant_id', $tenant->id)->where('status', 'pending')->count() === 0) {
                $tenant->update(['onboarding_completed_at' => now()]);
                return redirect()->route('dashboard', ['tenant_slug' => $tenant->slug]);
            }
            
            $currentStep = OnboardingStep::where('tenant_id', $tenant->id)
                ->where('status', 'pending')
                ->orderBy('id', 'asc')
                ->first();
        }

        return view('onboarding.steps.' . $currentStep->step, [
            'step' => $currentStep,
            'steps' => $this->steps,
            'tenant' => $tenant,
            'currentStepIndex' => $this->steps[$currentStep->step],
            'allSteps' => OnboardingStep::where('tenant_id', $tenant->id)->orderBy('id', 'asc')->get()
        ]);
    }

    public function storeStep(Request $request, $stepName)
    {
        $tenant = auth()->user()->tenant;
        $step = OnboardingStep::where('tenant_id', $tenant->id)->where('step', $stepName)->firstOrFail();

        if ($request->hasFile('csv_file')) {
            return $this->handleCsvUpload($request, $stepName, $tenant);
        }

        return $this->handleManualInput($request, $stepName, $tenant);
    }

    private function handleCsvUpload(Request $request, $stepName, $tenant)
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt']);
        $path = $request->file('csv_file')->path();
        $file = fopen($path, 'r');
        $header = fgetcsv($file);
        
        $data = [];
        while (($row = fgetcsv($file)) !== false) {
           if (count($header) === count($row)) {
               $data[] = array_combine($header, $row);
           }
        }
        fclose($file);

        DB::beginTransaction();
        try {
            foreach ($data as $item) {
                switch ($stepName) {
                    case 'area':
                        Area::firstOrCreate(['name' => $item['Name']]);
                        break;
                    case 'staff':
                        $areaId = Area::withoutGlobalScope('tenant')
                            ->where(function($q) use ($item, $tenant) {
                                $q->where('name', $item['Area'])
                                  ->where(function($qq) use ($tenant) {
                                      $qq->whereNull('tenant_id')->orWhere('tenant_id', $tenant->id);
                                  });
                            })->first()?->id;
                        $user = User::create([
                            'name' => $item['UserID'],
                            'phone_number' => $item['Phone Number'],
                            'password' => Hash::make($item['Password']),
                            'role' => Str::lower(str_replace(' ', '_', $item['Role'])),
                            'area_id' => $areaId,
                        ]);
                        Staff::create([
                            'user_id' => $user->id,
                            'name' => $item['Name'],
                            'phone_number' => $item['Phone Number'],
                            'area_id' => $areaId,
                        ]);
                        break;
                    case 'service_category':
                        ServiceCategory::firstOrCreate(['name' => $item['Categories Name']]);
                        break;
                    case 'service':
                        $catName = $item['Category'] ?? $item['Categories Name'] ?? null;
                        $catId = ServiceCategory::where('name', $catName)->first()?->id;
                        Service::create([
                            'name' => $item['Service Name'],
                            'category_id' => $catId,
                            'price' => (float)$item['Price'],
                            'cost' => (float)$item['Cost'],
                            'description' => $item['Description'] ?? '',
                        ]);
                        break;
                    case 'customer':
                        $customer = Customer::firstOrCreate(
                            ['phone_number' => $item['Customer Phone Number']],
                            ['name' => $item['Customer Name']]
                        );
                        $areaId = Area::withoutGlobalScope('tenant')
                            ->where(function($q) use ($item, $tenant) {
                                $q->where('name', $item['Area'])
                                  ->where(function($qq) use ($tenant) {
                                      $qq->whereNull('tenant_id')->orWhere('tenant_id', $tenant->id);
                                  });
                            })->first()?->id;
                        Address::create([
                            'customer_id' => $customer->id,
                            'label' => $item['Address Label'] ?? 'Home',
                            'contact_name' => $item['Contact Name'] ?? $customer->name,
                            'contact_phone' => $item['Contact Phone'] ?? $customer->phone_number,
                            'full_address' => $item['Full Address'] ?? '',
                            'google_maps_link' => $item['Google Maps Link'] ?? '',
                            'area_id' => $areaId,
                        ]);
                        break;
                }
            }
            DB::commit();
            return $this->completeStep($request, $stepName);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Import failed: ' . $e->getMessage()], 422);
        }
    }

    private function handleManualInput(Request $request, $stepName, $tenant)
    {
        $tenantId = auth()->user()->tenant_id;
        
        switch ($stepName) {
            case 'area':
                $validated = $request->validate(['name' => ['required', 'string', Rule::unique('areas')->where('tenant_id', $tenantId)]]);
                Area::create($validated);
                break;
            case 'staff':
                $validated = $request->validate([
                    'name' => 'required|string',
                    'phone_number' => 'required|string',
                    'role' => 'required|string',
                    'area_id' => 'required|exists:areas,id',
                    'password' => 'required|min:4'
                ]);
                $user = User::create([
                    'name' => $validated['name'],
                    'phone_number' => $validated['phone_number'],
                    'password' => Hash::make($validated['password']),
                    'role' => $validated['role'],
                    'area_id' => $validated['area_id'],
                ]);
                Staff::create([
                    'user_id' => $user->id,
                    'name' => $validated['name'],
                    'phone_number' => $validated['phone_number'],
                    'area_id' => $validated['area_id'],
                ]);
                break;
            case 'service_category':
                $validated = $request->validate(['name' => ['required', 'string', Rule::unique('service_categories')->where('tenant_id', $tenantId)]]);
                ServiceCategory::create($validated);
                break;
            case 'service':
                $validated = $request->validate([
                    'name' => ['required', 'string', Rule::unique('services')->where('tenant_id', $tenantId)],
                    'category_id' => 'required|exists:service_categories,id',
                    'price' => 'required|numeric',
                    'cost' => 'required|numeric',
                    'description' => 'nullable|string'
                ]);
                Service::create($validated);
                break;
            case 'customer':
                $validated = $request->validate([
                    'name' => 'required|string',
                    'phone_number' => 'required|string',
                    'label' => 'required|string',
                    'full_address' => 'required|string',
                    'area_id' => 'required|exists:areas,id'
                ]);
                $customer = Customer::firstOrCreate(['phone_number' => $validated['phone_number']], ['name' => $validated['name']]);
                Address::create([
                    'customer_id' => $customer->id,
                    'label' => $validated['label'],
                    'contact_name' => $validated['name'],
                    'contact_phone' => $validated['phone_number'],
                    'full_address' => $validated['full_address'],
                    'area_id' => $validated['area_id']
                ]);
                break;
            case 'settings':
                $validated = $request->validate([
                    'logo' => 'nullable|image|max:2048',
                    'invoice_text' => 'nullable|string',
                    'company_address' => 'nullable|string',
                ]);
                
                $tenant = auth()->user()->tenant;
                $settings = $tenant->settings ?? [];
                
                if ($request->hasFile('logo')) {
                    $settings['logo'] = $request->file('logo')->store('custom_branding', 'public');
                }
                
                $settings['invoice_text'] = $validated['invoice_text'] ?? ($settings['invoice_text'] ?? '');
                $settings['company_address'] = $validated['company_address'] ?? ($settings['company_address'] ?? '');
                
                $tenant->update(['settings' => $settings]);
                break;
            case 'password':
                $validated = $request->validate([
                    'password' => 'required|confirmed|min:6'
                ]);
                $user = auth()->user();
                $user->update(['password' => Hash::make($validated['password'])]);
                break;
        }

        return $this->completeStep($request, $stepName);
    }

    private function initializeSteps(Tenant $tenant)
    {
        foreach (array_keys($this->steps) as $stepName) {
            OnboardingStep::firstOrCreate([
                'tenant_id' => $tenant->id,
                'step' => $stepName,
            ], [
                'status' => 'pending',
            ]);
        }
    }

    public function completeStep(Request $request, $stepName)
    {
        $tenant = auth()->user()->tenant;
        $step = OnboardingStep::where('tenant_id', $tenant->id)->where('step', $stepName)->firstOrFail();

        // Handle specific logic for each step if needed before completing
        // Most logic will be in individual store methods (CSV/Manual)
        
        $step->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // If it was the last step, mark tenant as completed
        if ($stepName === 'password') {
            $tenant->update(['onboarding_completed_at' => now()]);
            return response()->json(['redirect' => route('dashboard', ['tenant_slug' => $tenant->slug])]);
        }

        return response()->json(['next' => route('onboarding.index', ['tenant_slug' => $tenant->slug])]);
    }
    
    public function reset(Tenant $tenant)
    {
        // Power to reset (for admin/superadmin)
        // Ensure only admin can do this
        // if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'superadmin') {
        //     abort(403);
        // }

        $tenant->update(['onboarding_completed_at' => null]);
        OnboardingStep::where('tenant_id', $tenant->id)->update([
            'status' => 'pending',
            'completed_at' => null,
            'metadata' => null
        ]);

        return redirect()->back()->with('success', 'Onboarding has been reset.');
    }

    // CSV Template Download
    public function downloadTemplate($type)
    {
        $templates = [
            'area' => [
                ['Name'],
                ['Jakarta Selatan'],
                ['Tangerang Selatan'],
            ],
            'staff' => [
                ['Name', 'Phone Number', 'Area', 'UserID', 'Password', 'Role'],
                ['Budi Santoso', '08123456789', 'Jakarta Selatan', 'budi.admin', 'password123', 'Admin'],
                ['Siti Aminah', '08987654321', 'Jakarta Selatan', 'siti.staff', 'password456', 'Staff'],
            ],
            'service_category' => [
                ['Categories Name'],
                ['Cleaning'],
                ['Maintenance'],
            ],
            'service' => [
                ['Service Name', 'Category', 'Price', 'Cost', 'Description'],
                ['AC Maintenance', 'Maintenance', '150000', '50000', 'Standard AC cleaning and checking'],
                ['House Deep Clean', 'Cleaning', '500000', '200000', 'Full deep cleaning of house'],
            ],
            'customer' => [
                ['Customer Name', 'Customer Phone Number', 'Address Label', 'Contact Name', 'Contact Phone', 'Full Address', 'Google Maps Link', 'Area'],
                ['PT Cahaya Abadi', '021555123', 'Office HQ', 'Joko Susilo', '08111222333', 'Jl. Sudirman No. 45, Jakarta', 'https://maps.google.com/?q=-6.2,106.8', 'Jakarta Selatan'],
                ['Ibu Maria', '08129998887', 'Home', 'Maria', '08129998887', 'Perumahan Indah Blok A, Tangerang', 'https://maps.google.com/?q=-6.3,106.7', 'Tangerang Selatan'],
            ],
        ];

        if (!isset($templates[$type])) abort(404);

        $filename = "template_{$type}.csv";
        $handle = fopen('php://temp', 'w+');
        foreach ($templates[$type] as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }
}
