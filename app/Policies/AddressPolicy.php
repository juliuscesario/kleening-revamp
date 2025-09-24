<?php

namespace App\Policies;

use App\Models\Address;
use App\Models\User;

class AddressPolicy
{
    // Owner bisa melakukan apa saja
    public function before(User $user, string $ability): bool|null
    {
        if (strtolower($user->role) === 'owner') {
            return true;
        }
        return null;
    }

    // Siapa yang boleh melihat daftar alamat? Semua orang (tapi akan difilter)
    public function viewAny(User $user): bool
    {
        return true;
    }

    // Siapa yang boleh melihat detail satu alamat?
    public function view(User $user, Address $address): bool
    {
        return true;
    }

    // Siapa yang boleh membuat alamat baru?
    public function create(User $user): bool
    {
        return in_array($user->role, ['owner', 'co_owner', 'admin']);
    }

    // Siapa yang boleh update alamat?
    public function update(User $user, Address $address): bool
    {
        return in_array($user->role, ['co_owner', 'admin']);
    }

    // Siapa yang boleh hapus alamat?
    public function delete(User $user, Address $address): bool
    {
        return in_array($user->role, ['co_owner', 'admin']);
    }
}