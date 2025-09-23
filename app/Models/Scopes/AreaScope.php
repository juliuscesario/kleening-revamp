<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class AreaScope implements Scope
{
    /**
     * The tables that are restricted by area for co-owners.
     *
     * @var array
     */
    protected $areaRestrictedTables = [
        'staff',
        'service_orders',
        'customers',
        'addresses',
    ];

    public function apply(Builder $builder, Model $model)
    {
        $user = Auth::user();

        // Only apply this scope for authenticated co-owners
        if (!$user || $user->role !== 'co_owner') {
            return;
        }

        $areaId = $user->area_id;
        $tableName = $model->getTable();

        // If the table is not area-restricted, do nothing.
        // This allows co-owners to see master data like areas, service_categories, etc.
        if (!in_array($tableName, $this->areaRestrictedTables)) {
            return;
        }

        // If a co-owner has no area, they should not see any area-restricted data.
        if (!$areaId) {
            $builder->whereRaw('1 = 0'); // Force query to return no results
            return;
        }

        // Apply area restrictions for specific tables
        if ($tableName == 'staff') {
            $builder->where($model->getTable() . '.area_id', $areaId);
        }

        if ($tableName == 'service_orders') {
            $builder->whereHas('staff', function($query) use ($areaId) {
                $query->where('area_id', $areaId);
            });
        }

        if ($tableName == 'customers') {
            $builder->whereHas('serviceOrders.staff', function($query) use ($areaId) {
                $query->where('area_id', $areaId);
            });
        }

        if ($tableName == 'addresses') {
            $builder->whereHas('customer.serviceOrders.staff', function($query) use ($areaId) {
                $query->where('area_id', $areaId);
            });
        }
    }
}
