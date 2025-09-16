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
}