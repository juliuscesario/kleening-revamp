<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerPolicy
{
    // Di dalam class CustomerPolicy
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Customer $customer): bool { return true; }
    public function create(User $user): bool
    {
        return in_array($user->role, ['owner', 'co_owner', 'admin']);
    }
    public function update(User $user, Customer $customer): bool
    {
        return in_array($user->role, ['owner', 'co_owner', 'admin']);
    }
    public function delete(User $user, Customer $customer): bool
    {
        return in_array($user->role, ['owner', 'co_owner', 'admin']);
    }
}
