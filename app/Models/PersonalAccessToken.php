<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;
use App\Traits\BelongsToTenant;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    use BelongsToTenant;

    protected $fillable = [
        'name',
        'token',
        'abilities',
        'last_used_at',
        'expires_at',
        'tenant_id',
    ];
}
