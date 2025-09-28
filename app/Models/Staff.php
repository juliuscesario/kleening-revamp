<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\AreaScope; // <-- INI YANG BENAR

class Staff extends Model
{
    use HasFactory;
    
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
}