<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class AreaScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model)
    {
        // Cek jika ada user yang sedang login dan rolenya adalah 'co_owner'
        if (Auth::check() && Auth::user()->role == 'co_owner') {
            // Dapatkan area_id dari co_owner yang login
            $areaId = Auth::user()->area_id;

            // Cek apakah tabel yang sedang di-query memiliki kolom 'area_id'
            // Ini akan berlaku untuk model Staff
            if ($model->getTable() == 'staff') {
                $builder->where('area_id', $areaId);
            }

            // Untuk ServiceOrder, filternya lebih kompleks karena SO tidak punya area_id langsung.
            // Kita filter berdasarkan area dari staff yang ditugaskan.
            if ($model->getTable() == 'service_orders') {
                $builder->whereHas('staff', function($query) use ($areaId) {
                    $query->where('area_id', $areaId);
                });
            }
        }
    }
}