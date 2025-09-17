<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkPhoto;
use Illuminate\Auth\Access\Response;

class WorkPhotoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WorkPhoto $workPhoto): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, ServiceOrder $serviceOrder): bool
    {
        if (in_array($user->role, ['owner', 'co_owner', 'admin'])) {
            return true;
        }
        // Staff hanya boleh upload jika ditugaskan ke SO tersebut
        if ($user->role == 'staff' && $user->staff) {
            return $serviceOrder->staff()->where('staff_id', $user->staff->id)->exists();
        }
        return false;
    }

   /**
     * Determine whether the user can update models.
     */
    public function update(User $user, ServiceOrder $serviceOrder): bool
    {
        if (in_array($user->role, ['owner', 'co_owner', 'admin'])) {
            return true;
        }
        // Staff hanya boleh upload jika ditugaskan ke SO tersebut
        if ($user->role == 'staff' && $user->staff) {
            return $serviceOrder->staff()->where('staff_id', $user->staff->id)->exists();
        }
        return false;
    }

    /**
     * Determine whether the user can update models.
     */
    public function delete(User $user, ServiceOrder $serviceOrder): bool
    {
        if (in_array($user->role, ['owner', 'co_owner', 'admin'])) {
            return true;
        }
        // Staff hanya boleh upload jika ditugaskan ke SO tersebut
        if ($user->role == 'staff' && $user->staff) {
            return $serviceOrder->staff()->where('staff_id', $user->staff->id)->exists();
        }
        return false;
    }
    
}
