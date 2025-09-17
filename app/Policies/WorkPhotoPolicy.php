<?php

namespace App\Policies;

use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\WorkPhoto;

class WorkPhotoPolicy
{
    // Owner bisa melakukan apa saja
    public function before(User $user, string $ability): bool|null
    {
        if (strtolower($user->role) === 'owner') {
            return true;
        }
        return null;
    }

    // Siapa yang boleh upload foto ke sebuah SO?
    public function create(User $user, ServiceOrder $serviceOrder): bool
    {
        // Admin & Co-Owner boleh
        if (in_array($user->role, ['co_owner', 'admin'])) {
            return true;
        }
        // Staff boleh jika ditugaskan ke SO ini
        if ($user->role == 'staff' && $user->staff) {
            return $serviceOrder->staff()->where('staff_id', $user->staff->id)->exists();
        }
        return false;
    }

    // Siapa yang boleh hapus foto?
    public function delete(User $user, WorkPhoto $workPhoto): bool
    {
        // Admin & Co-Owner boleh
        if (in_array($user->role, ['co_owner', 'admin'])) {
            return true;
        }
        // Staff boleh jika dia yang mengupload
        if ($user->role == 'staff') {
            return $workPhoto->uploaded_by === $user->id;
        }
        return false;
    }
}