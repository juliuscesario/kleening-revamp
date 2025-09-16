<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'label',
        'contact_name',
        'contact_phone',
        'full_address',
        'google_maps_link',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}