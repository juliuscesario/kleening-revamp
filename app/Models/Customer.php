<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute; // <-- TAMBAHKAN INI
use App\Models\Scopes\AreaScope; // <-- INI YANG BENAR
use App\Models\Area;
use App\Models\Address;

class Customer extends Model
{
    use HasFactory, SoftDeletes;
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new AreaScope);
    }

    protected $fillable = ['name', 'phone_number'];

    protected $casts = [
        'last_order_date' => 'datetime',
    ];

    /**
     * Mutator untuk nama customer (auto upper case).
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => strtoupper($value),
        );
    }

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

    public function invoices()
    {
        return $this->hasManyThrough(Invoice::class, ServiceOrder::class);
    }

    /**
     * Get all of the areas for the Customer through their addresses.
     */
    public function areas()
    {
        return $this->hasManyThrough(Area::class, Address::class);
    }

    /**
     * Recalculate and persist last_order_date from related service orders.
     * Call this after creating/updating/deleting a service order for this customer.
     */
    public function syncLastOrderDate(): void
    {
        $this->withoutEvents(function () {
            $this->update([
                'last_order_date' => $this->serviceOrders()
                    ->whereNotIn('status', ['cancelled'])
                    ->max('work_date'),
            ]);
        });
    }

}