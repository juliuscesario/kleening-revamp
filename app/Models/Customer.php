<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute; // <-- TAMBAHKAN INI

class Customer extends Model
{
    use HasFactory;
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new AreaScope);
    }
    
    protected $fillable = ['name', 'phone_number'];

    /**
     * Satu Customer bisa memiliki banyak Address.
     */
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Satu Customer bisa memiliki banyak Service Order.
     */
    public function serviceOrders()
    {
        return $this->hasMany(ServiceOrder::class);
    }

    /**
     * Accessor untuk mendapatkan tanggal order terakhir secara dinamis.
     */
    protected function lastOrderDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->serviceOrders()->latest('work_date')->first()?->work_date,
        );
    }
    
}