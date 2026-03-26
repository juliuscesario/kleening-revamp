<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'domain',
        'settings',
        'onboarding_completed_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'onboarding_completed_at' => 'datetime',
    ];

    /**
     * Get the URL for this tenant.
     */
    public function getUrlAttribute(): string
    {
        if ($this->domain) {
            return 'https://' . $this->domain;
        }

        return 'https://' . $this->slug . '.' . config('app.central_domain');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function areas()
    {
        return $this->hasMany(Area::class);
    }

    public function onboardingSteps()
    {
        return $this->hasMany(OnboardingStep::class);
    }
}
