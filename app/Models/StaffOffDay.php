<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffOffDay extends Model
{
    protected $fillable = [
        'staff_id',
        'off_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'off_date' => 'date',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
