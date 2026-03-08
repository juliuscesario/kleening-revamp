<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateTenantCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create {name} {slug} {phone} {password} {domain?}';

    protected $description = 'Create a new tenant with an owner user.';

    public function handle()
    {
        $name = $this->argument('name');
        $slug = $this->argument('slug');
        $phone = $this->argument('phone');
        $password = $this->argument('password');
        $domain = $this->argument('domain');

        \Illuminate\Support\Facades\DB::transaction(function () use ($name, $slug, $phone, $password, $domain) {
            $tenant = \App\Models\Tenant::create([
                'name' => $name,
                'slug' => $slug,
                'domain' => $domain,
            ]);

            // Temporarily set currentTenant so scoping works for user creation
            app()->instance('currentTenant', $tenant);

            \App\Models\User::create([
                'name' => 'Owner of ' . $name,
                'phone_number' => $phone,
                'password' => \Illuminate\Support\Facades\Hash::make($password),
                'role' => 'owner',
            ]);
        });

        $this->info("Tenant '{$name}' created successfully with owner phone '{$phone}'.");
        $this->info("Access it at: " . ($domain ?: $slug . '.' . config('app.central_domain')));
    }
}
