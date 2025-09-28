<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // <-- INI KUNCINYA
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use HasFactory; // <-- Baris ini butuh 'use' statement di atas

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name']; // <-- Ini juga penting untuk method create

    public function services()
    {
        return $this->hasMany(Service::class, 'category_id');
    }

    public function serviceOrderItems()
    {
        return $this->hasManyThrough(ServiceOrderItem::class, Service::class, 'category_id', 'service_id', 'id', 'id');
    }
}