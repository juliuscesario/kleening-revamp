<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    // Method ini otomatis memberi akses penuh ke owner
    public function before(User $user, string $ability): bool|null
    {
        if (strtolower($user->role) === 'owner') {
            return true;
        }
        return null;
    }

    // Siapa yang boleh melihat daftar invoice?
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['co_owner', 'admin']);
    }

    // Siapa yang boleh melihat detail satu invoice?
    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->role === 'co_owner') {
            return $invoice->serviceOrder->address->area_id === $user->area_id;
        }
        return in_array($user->role, ['admin']);
    }

    // Siapa yang boleh membuat invoice?
    public function create(User $user): bool
    {
        return in_array($user->role, ['co_owner', 'admin']);
    }

    // Siapa yang boleh update invoice?
    public function update(User $user, Invoice $invoice): bool
    {
        if ($user->role === 'co_owner') {
            return $invoice->serviceOrder->address->area_id === $user->area_id;
        }
        return in_array($user->role, ['admin']);
    }

    // Siapa yang boleh hapus invoice?
    public function delete(User $user, Invoice $invoice): bool
    {
        // Pembatalan invoice dibatasi untuk owner lewat metode before()
        return false;
    }
}
