<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'so_number',
        'customer_id',
        'address_id',
        'work_date',
        'status',
        'work_notes',
        'staff_notes',
        'created_by',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function items()
    {
        return $this->hasMany(ServiceOrderItem::class);
    }

    public function staff()
    {
        // Relasi Many-to-Many melalui tabel pivot service_order_staff
        return $this->belongsToMany(Staff::class, 'service_order_staff');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}