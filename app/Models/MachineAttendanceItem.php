<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineAttendanceItem extends Model
{
    use HasFactory;

    protected $fillable = ['machine_attendance_id', 'machine_id'];

    public function attendance()
    {
        return $this->belongsTo(MachineAttendance::class, 'machine_attendance_id');
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
}
