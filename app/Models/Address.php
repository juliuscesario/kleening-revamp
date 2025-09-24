<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\AreaScope; // <-- INI YANG BENAR

class Address extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new AreaScope);
    }
    
    protected $fillable = [
        'customer_id',
        'area_id',
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

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
    
}