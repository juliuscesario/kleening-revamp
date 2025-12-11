<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\AreaScope;
use App\Models\User; // Import User model
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash; // Import Hash facade

class ServiceOrder extends Model
{
    use HasFactory;

    // Define status constants
    public const STATUS_BOOKED = 'booked';
    public const STATUS_PROSES = 'proses';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_DONE = 'done';
    public const STATUS_INVOICED = 'invoiced';

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new AreaScope);
    }
    
    protected $fillable = [
        'so_number',
        'customer_id',
        'address_id',
        'work_date',
        'work_time',
        'status',
        'work_notes',
        'staff_notes',
        'created_by',
        'work_proof_completed_at',
        'customer_signature_image',
    ];

    protected $casts = [
        'work_date' => 'date',
        'work_proof_completed_at' => 'datetime',
    ];

    /**
     * Check if a status transition is valid.
     *
     * @param string $newStatus
     * @param User|null $user
     * @param string|null $ownerPassword
     * @return array ['allowed' => bool, 'message' => string]
     */
    public function canTransitionTo(string $newStatus, ?User $user = null, ?string $ownerPassword = null): array
    {
        $currentStatus = $this->status;

        // Valid statuses
        $validStatuses = [
            self::STATUS_BOOKED,
            self::STATUS_PROSES,
            self::STATUS_CANCELLED,
            self::STATUS_DONE,
            self::STATUS_INVOICED
        ];

        if (!in_array($newStatus, $validStatuses)) {
            return ['allowed' => false, 'message' => 'Invalid new status provided.'];
        }

        // No change in status is always allowed (or handled elsewhere if specific actions are needed)
        if ($currentStatus === $newStatus) {
            return ['allowed' => true, 'message' => 'Status is already ' . $newStatus . '.'];
        }

        switch ($currentStatus) {
            case self::STATUS_BOOKED:
                // Booked can go to Proses or Cancelled
                if ($newStatus === self::STATUS_PROSES || $newStatus === self::STATUS_CANCELLED) {
                    return ['allowed' => true, 'message' => ''];
                }
                return ['allowed' => false, 'message' => 'Status "booked" can only transition to "proses" or "cancelled".'];

            case self::STATUS_PROSES:
                // Proses can go to Cancelled or Done
                if ($newStatus === self::STATUS_CANCELLED) {
                    // Proses to Cancelled requires owner approval
                    if (!$user || $user->role !== 'owner') {
                        return ['allowed' => false, 'message' => 'Only owner can change status from "proses" to "cancelled".'];
                    }
                    return ['allowed' => true, 'message' => ''];
                } elseif ($newStatus === self::STATUS_DONE) {
                    return ['allowed' => true, 'message' => ''];
                }
                return ['allowed' => false, 'message' => 'Status "proses" can only transition to "cancelled" or "done".'];

            case self::STATUS_INVOICED:
            case self::STATUS_CANCELLED:
            case self::STATUS_DONE:
                // Invoiced, Cancelled, and Done are terminal states
                return ['allowed' => false, 'message' => 'Status "' . $currentStatus . '" cannot be changed.'];

            default:
                return ['allowed' => false, 'message' => 'Unknown current status.'];
        }
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function items()
    {
        return $this->hasMany(ServiceOrderItem::class);
    }

    public function staff()
    {
        return $this->belongsToMany(Staff::class, 'service_order_staff')->withPivot('signature_image');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Mendefinisikan relasi "hasOne" ke Invoice.
     * Satu ServiceOrder hanya memiliki satu Invoice.
     */
    public function invoice() // <-- TAMBAHKAN METHOD INI
    {
        return $this->hasOne(Invoice::class)->withoutGlobalScope(AreaScope::class);
    }

    /**
     * Mendefinisikan relasi "hasOne" ke Invoice.
     * Satu ServiceOrder hanya memiliki satu Invoice.
     */
    public function workPhotos() // <-- TAMBAHKAN METHOD INI
    {
        return $this->hasMany(WorkPhoto::class);
    }

    /**
     * Format stored work_time into HH:MM (WIB) for display.
     */
    public function getWorkTimeFormattedAttribute(): ?string
    {
        if (!$this->work_time) {
            return null;
        }

        try {
            return Carbon::createFromFormat('H:i:s', $this->work_time)->format('H:i');
        } catch (\Throwable $e) {
            return $this->work_time;
        }
    }
}
