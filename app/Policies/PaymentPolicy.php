<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    // Method ini otomatis memberi akses penuh ke owner
    public function before(User $user, string $ability): bool|null
    {
        if (strtolower($user->role) === 'owner') {
            return true;
        }
        return null;
    }

    // Siapa yang boleh melihat daftar payment?
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['co_owner', 'admin']);
    }

    // Siapa yang boleh melihat detail satu payment?
    public function view(User $user, Payment $payment): bool
    {
        if ($user->role === 'co_owner') {
            return $payment->invoice->serviceOrder->address->area_id === $user->area_id;
        }
        return in_array($user->role, ['admin']);
    }

    // Siapa yang boleh membuat payment?
    public function create(User $user): bool
    {
        return in_array($user->role, ['co_owner', 'admin']);
    }

    // Siapa yang boleh update payment?
    public function update(User $user, Payment $payment): bool
    {
        if ($user->role === 'co_owner') {
            return $payment->invoice->serviceOrder->address->area_id === $user->area_id;
        }
        return in_array($user->role, ['admin']);
    }

    // Siapa yang boleh hapus payment?
    public function delete(User $user, Payment $payment): bool
    {
        if ($user->role === 'co_owner') {
            return $payment->invoice->serviceOrder->address->area_id === $user->area_id;
        }
        return in_array($user->role, ['admin']);
    }
}