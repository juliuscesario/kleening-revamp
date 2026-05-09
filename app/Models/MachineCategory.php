<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'code_prefix', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function machines()
    {
        return $this->hasMany(Machine::class, 'category_id');
    }
}
