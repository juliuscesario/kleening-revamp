<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['name', 'tenant_id'];

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'category_id');
    }
}
