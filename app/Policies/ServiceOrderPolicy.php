<?php

namespace App\Policies;

use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ServiceOrderPolicy
{
    // Di dalam class CustomerPolicy
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, ServiceOrder $serviceOrder): bool
    {
        if (in_array($user->role, ['owner', 'co_owner', 'admin'])) {
            return true;
        }

        // Staff can only view service orders assigned to them
        if ($user->role == 'staff' && $user->staff) {
            return $serviceOrder->staff()->where('staff.id', $user->staff->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can view the staff-specific details of the service order.
     */
    public function viewStaffDetails(User $user, ServiceOrder $serviceOrder): bool
    {
        // Staff can view staff-specific details if they are assigned to the service order
        if ($user->role == 'staff' && $user->staff) {
            return $serviceOrder->staff()->where('staff.id', $user->staff->id)->exists();
        }
        return false;
    }
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

    /**
     * Determine whether the user can start work on the service order.
     */
    public function startWork(User $user, ServiceOrder $serviceOrder): bool
    {
        // Only staff assigned to the service order can start work
        if ($user->role == 'staff' && $user->staff) {
            return $serviceOrder->staff()->where('staff.id', $user->staff->id)->exists();
        }
        return false;
    }

    /**
     * Determine whether the user can upload work proof photos for the service order.
     */
    public function uploadWorkProof(User $user, ServiceOrder $serviceOrder): bool
    {
        // Only staff assigned to the service order can upload work proof
        if ($user->role == 'staff' && $user->staff) {
            return $serviceOrder->staff()->where('staff.id', $user->staff->id)->exists();
        }
        return false;
    }

    public function delete(User $user, ServiceOrder $serviceorder): bool
    {
        return in_array($user->role, ['owner', 'co_owner', 'admin']);
    }
}
