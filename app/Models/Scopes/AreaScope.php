<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class AreaScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (Auth::check() && Auth::user()->role == 'co_owner') {
            $areaId = Auth::user()->area_id;
            $tableName = $model->getTable();

            if ($tableName == 'staff') {
                $builder->where('area_id', $areaId);
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
}