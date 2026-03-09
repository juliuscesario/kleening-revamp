<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

trait BelongsToTenant
{
    /**
     * Boot the trait.
     */
    protected static function bootBelongsToTenant(): void
    {
        static::creating(function (Model $model) {
            $tenantId = static::getTenantIdForScoping();

            if (!$model->tenant_id && $tenantId) {
                $model->tenant_id = $tenantId;
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = static::getTenantIdForScoping();

            if ($tenantId) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
            }
        });
    }

    protected static function getTenantIdForScoping(): ?int
    {
        if (app()->has('currentTenant')) {
            return app('currentTenant')->id;
        }

        // Avoid infinite recursion by checking if the user is already resolved
        if (auth()->hasUser()) {
            $user = auth()->user();
            if ($user && $user->tenant_id && $user->role !== 'superadmin') {
                return $user->tenant_id;
            }
        }

        return null;
    }

    /**
     * Get the tenant that owns the model.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
