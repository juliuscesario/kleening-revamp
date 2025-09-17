<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InvoicePolicy
{
    // Di dalam class CustomerPolicy
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Invoice $invoice): bool { return true; }
    public function create(User $user): bool
    {
        return in_array($user->role, ['owner', 'co_owner', 'admin']);
    }
    public function update(User $user, Invoice $invoice): bool
    {
        return in_array($user->role, ['owner', 'co_owner', 'admin']);
    }
    public function delete(User $user, Invoice $invoice): bool
    {
        return in_array($user->role, ['owner', 'co_owner', 'admin']);
    }
}
