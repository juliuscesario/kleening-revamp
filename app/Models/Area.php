<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // <-- INI KUNCINYA
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory; // <-- Baris ini butuh 'use' statement di atas

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name']; // <-- Ini juga penting untuk method create

    /**
     * Get all of the addresses for the Area.
     */
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Get all of the service orders for the Area.
     */
    public function serviceOrders()
    {
        return $this->hasManyThrough(ServiceOrder::class, Address::class);
    }

    /**
     * Get all of the invoices for the Area.
     */
    public function invoices()
    {
        return $this->hasManyThrough(Invoice::class, ServiceOrder::class, 'address_id', 'service_order_id', 'id', 'id');
    }
}