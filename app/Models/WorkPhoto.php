<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class WorkPhoto extends Model
{
    use HasFactory;

    public $timestamps = false; // Kita hanya pakai created_at

    protected $fillable = [
        'service_order_id',
        'file_path',
        'type',
        'uploaded_by',
    ];
    
    // Ini otomatis menambahkan 'created_at' saat data dibuat
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }

    /**
     * Accessor untuk mendapatkan URL lengkap ke file foto.
     */
    protected function photoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => Storage::url($this->file_path),
        );
    }
    
    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}