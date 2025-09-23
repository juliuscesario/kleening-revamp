<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class AreaScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $user = Auth::user();

        // Only apply this scope for authenticated co-owners
        if (!$user || $user->role !== 'co_owner') {
            return;
        }

        $areaId = $user->area_id;
        $tableName = $model->getTable();

        // If a co-owner has no area, they should not see any area-restricted data.
        if (!$areaId) {
            $builder->whereRaw('1 = 0'); // Force query to return no results
            return;
        }

        // --- New Simplified Logic ---

        // If the model is Address, filter directly
        if ($tableName === 'addresses') {
            $builder->where('area_id', $areaId);
        }

        // If the model is Customer, filter via their addresses
        if ($tableName === 'customers') {
            $builder->whereHas('addresses', function ($query) use ($areaId) {
                $query->where('area_id', $areaId);
            });
        }

        // If the model is ServiceOrder, filter via its address
        if ($tableName === 'service_orders') {
            $builder->whereHas('address', function ($query) use ($areaId) {
                $query->where('area_id', $areaId);
            });
        }

        // If the model is Staff, filter directly by their own area_id
        if ($tableName === 'staff') {
            $builder->where('area_id', $areaId);
        }

        // If the model is Invoice, filter via the ServiceOrder's address
        if ($tableName === 'invoices') {
            $builder->whereHas('serviceOrder.address', function ($query) use ($areaId) {
                $query->where('area_id', $areaId);
            });
        }
    }
}