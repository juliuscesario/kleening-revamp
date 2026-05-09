<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'date',
        'photo_pergi',
        'photo_pergi_at',
        'photo_pulang',
        'photo_pulang_at',
        'catatan',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date' => 'date',
        'photo_pergi_at' => 'datetime',
        'photo_pulang_at' => 'datetime',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function items()
    {
        return $this->hasMany(MachineAttendanceItem::class);
    }

    public function machines()
    {
        return $this->belongsToMany(Machine::class, 'machine_attendance_items');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Check if a staff member has an active attendance today (pergi done).
     * Used by the SO Gate in Phase B3.
     */
    public static function hasActiveAttendanceToday(int $staffId): bool
    {
        return self::where('staff_id', $staffId)
            ->whereDate('date', now(config('app.timezone'))->toDateString())
            ->whereNotNull('photo_pergi_at')
            ->exists();
    }

    /**
     * Check if a staff member has an open attendance today (pergi done, pulang not done).
     */
    public static function getOpenAttendanceToday(int $staffId): ?self
    {
        return self::where('staff_id', $staffId)
            ->whereDate('date', now(config('app.timezone'))->toDateString())
            ->whereNotNull('photo_pergi_at')
            ->whereNull('photo_pulang_at')
            ->with('machines.category')
            ->first();
    }
}
