<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'service_order_id',
        'service_id',
        'quantity',
        'price',
        'total',
    ];

    /**
     * Disable timestamps for this model.
     *
     * @var bool
     */
    public $timestamps = false; // <-- Tambahkan ini karena kita tidak punya kolom created_at/updated_at di tabel ini

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }
}