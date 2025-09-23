<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'price',
        'description',
    ];

    /**
     * Mendefinisikan relasi "belongsTo" ke ServiceCategory.
     * Satu Service dimiliki oleh satu ServiceCategory.
     */
    public function category()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function serviceOrderItems()
    {
        return $this->hasMany(ServiceOrderItem::class);
    }
}