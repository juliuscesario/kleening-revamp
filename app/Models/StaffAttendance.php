<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'nik',
        'nama',
        'tanggal',
        'clock_in',
        'clock_out',
        'status',
        'raw_status',
        'notes',
        'clock_in_location',
        'clock_out_location',
        'hadirr_raw',
        'synced_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'synced_at' => 'datetime',
        'hadirr_raw' => 'array',
    ];

    /**
     * Map Hadirr NIK to local staff record.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'nik', 'hadirr_nik');
    }
}
