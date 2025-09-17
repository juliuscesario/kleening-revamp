<?php

namespace App\Policies;

use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ServiceOrderPolicy
{
    // Di dalam class CustomerPolicy
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, ServiceOrder $serviceorder): bool { return true; }
    public function create(User $user): bool
    {
        return in_array($user->role, ['owner', 'co_owner', 'admin']);
    }
    public function update(User $user, ServiceOrder $serviceorder): bool
    {
        return in_array($user->role, ['owner', 'co_owner', 'admin']);
    }
    /**
     * Tentukan apakah user boleh mengubah status SO.
     */
    public function updateStatus(User $user, ServiceOrder $serviceOrder): bool
    {
        // Izin diberikan jika user adalah staff DAN namanya ada di daftar staff SO tersebut.
        if ($user->role == 'staff' && $user->staff) {
            return $serviceOrder->staff()->where('staff_id', $user->staff->id)->exists();
        }
        return false;
    }
    public function delete(User $user, ServiceOrder $serviceorder): bool
    {
        return in_array($user->role, ['owner', 'co_owner', 'admin']);
    }
}
