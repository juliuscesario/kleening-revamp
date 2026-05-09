<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'category_id', 'area_id', 'status', 'paired_machine_id', 'notes'];

    public function category()
    {
        return $this->belongsTo(MachineCategory::class, 'category_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function pairedMachine()
    {
        return $this->belongsTo(Machine::class, 'paired_machine_id');
    }

    public function pairedBy()
    {
        return $this->hasOne(Machine::class, 'paired_machine_id');
    }

    public function attendanceItems()
    {
        return $this->hasMany(MachineAttendanceItem::class);
    }
}
