<?php

namespace App\Providers;

use App\Models\User; // <-- Pastikan ini ada
use Illuminate\Support\Facades\Gate; // <-- Pastikan ini ada
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\ServiceOrder::class => \App\Policies\ServiceOrderPolicy::class,
        \App\Models\ServiceCategory::class => \App\Policies\ServiceCategoryPolicy::class,
        \App\Models\Customer::class => \App\Policies\CustomerPolicy::class,
        \App\Models\Staff::class => \App\Policies\StaffPolicy::class,
        \App\Models\Area::class => \App\Policies\AreaPolicy::class,
        \App\Models\Service::class => \App\Policies\ServicePolicy::class,
        \App\Models\WorkPhoto::class => \App\Policies\WorkPhotoPolicy::class,
        \App\Models\Invoice::class => \App\Policies\InvoicePolicy::class,
        \App\Models\Address::class => \App\Policies\AddressPolicy::class,
        \App\Models\Payment::class => \App\Policies\PaymentPolicy::class,

    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Gate "Kartu Super" untuk Owner. 
        // Kode ini akan berjalan pertama. Jika user adalah owner, 
        // ia akan langsung diberi akses penuh ke semua fitur.
        
        Gate::before(function (User $user, string $ability) {
            if ($user->role == 'owner') {
                return true;
            }
        });

        // Gate untuk mengelola data master yang sensitif
        Gate::define('manage-master-data', function (User $user) {
            // Hanya 'owner' yang bisa, tapi karena ada Gate::before, 
            // kita bisa sederhanakan menjadi: BUKAN 'admin', BUKAN 'staff'
            return in_array($user->role, ['owner', 'co_owner']);
        });

        // Gate untuk melihat laporan
        Gate::define('view-reports', function (User $user) {
            // Owner dan co_owner bisa melihat laporan
            return in_array($user->role, ['owner', 'co_owner']);
        });
    }
}