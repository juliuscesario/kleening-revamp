<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MachineAttendance;

class MachineAttendancePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array(strtolower(trim($user->role)), ['owner', 'co_owner']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MachineAttendance $machineAttendance): bool
    {
        return in_array(strtolower(trim($user->role)), ['owner', 'co_owner']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MachineAttendance $machineAttendance): bool
    {
        return in_array(strtolower(trim($user->role)), ['owner', 'co_owner']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MachineAttendance $machineAttendance): bool
    {
        return in_array(strtolower(trim($user->role)), ['owner', 'co_owner']);
    }
}
