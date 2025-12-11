<?php

namespace App\Policies;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StaffPolicy
{
    // Method ini mengizinkan Owner untuk melakukan apa saja
    public function before(User $user, string $ability): bool|null
    {
        if ($user->role == 'owner') {
            return true;
        }
        return null;
    }

    // Siapa yang boleh melihat daftar staff? Semua orang (tapi akan difilter Scope)
    public function viewAny(User $user): bool
    {
        return true;
    }

    // Siapa yang boleh melihat detail satu staff? Semua orang
    public function view(User $user, Staff $staff): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['co_owner']);
    }

    // Siapa yang boleh update staff?
    public function update(User $user, Staff $staff): bool
    {
        return in_array($user->role, ['co_owner']);
    }

    // Siapa yang boleh hapus staff?
    public function delete(User $user, Staff $staff): bool
    {
        return false;
    }

    // Siapa yang boleh resign staff?
    public function resign(User $user, Staff $staff): bool
    {
        return in_array($user->role, ['co_owner']);
    }
}