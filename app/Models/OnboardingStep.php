<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingStep extends Model
{
    protected $fillable = [
        'tenant_id',
        'step',
        'status',
        'completed_at',
        'metadata',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
