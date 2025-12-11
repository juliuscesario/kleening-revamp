<?php

namespace App\Policies;

use App\Models\Area;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AreaPolicy
{
    // Di dalam class AreaPolicy
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Area $area): bool { return true; }
    public function create(User $user): bool { return false; } // Hanya owner (sudah di-handle Gate::before)
    public function update(User $user, Area $area): bool { return false; }
    public function delete(User $user, Area $area): bool { return false; }
}
