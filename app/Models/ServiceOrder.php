<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\AreaScope; // <-- INI YANG BENAR

class ServiceOrder extends Model
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
        return $this->belongsToMany(Staff::class, 'service_order_staff');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Mendefinisikan relasi "hasOne" ke Invoice.
     * Satu ServiceOrder hanya memiliki satu Invoice.
     */
    public function invoice() // <-- TAMBAHKAN METHOD INI
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * Mendefinisikan relasi "hasOne" ke Invoice.
     * Satu ServiceOrder hanya memiliki satu Invoice.
     */
    public function workPhotos() // <-- TAMBAHKAN METHOD INI
    {
        return $this->hasMany(WorkPhoto::class);
    }

    
}