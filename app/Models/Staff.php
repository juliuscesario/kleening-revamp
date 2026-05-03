<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\AreaScope; // <-- INI YANG BENAR

use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Resolve the route binding to prevent SQL errors for non-numeric IDs in PostgreSQL.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field === null || $field === $this->getKeyName()) {
            if (!is_numeric($value)) {
                return null;
            }
        }
        return parent::resolveRouteBinding($value, $field);
    }
    
    /**
     * The "booted" method of the model.
    */
    protected static function booted(): void
    {
        static::addGlobalScope(new AreaScope);
    }

    protected $fillable = [
        'user_id',
        'area_id',
        'name',
        'phone_number',
        'base_harian',
        'harian_tambahan',
        'is_active',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function serviceOrders()
    {
        return $this->belongsToMany(ServiceOrder::class, 'service_order_staff');
    }

    public function offDays()
    {
        return $this->hasMany(\App\Models\StaffOffDay::class);
    }
}