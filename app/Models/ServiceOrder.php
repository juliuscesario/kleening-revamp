<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\AreaScope;
use App\Models\User; // Import User model
use Illuminate\Support\Facades\Hash; // Import Hash facade

class ServiceOrder extends Model
{
    use HasFactory;

    // Define status constants
    public const STATUS_DIJADWALKAN = 'dijadwalkan';
    public const STATUS_PROSES = 'proses';
    public const STATUS_BATAL = 'batal';
    public const STATUS_SELESAI = 'selesai';
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
        'status',
        'work_notes',
        'staff_notes',
        'created_by',
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
            self::STATUS_DIJADWALKAN,
            self::STATUS_PROSES,
            self::STATUS_BATAL,
            self::STATUS_SELESAI,
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
            case self::STATUS_DIJADWALKAN:
                // Dijadwalkan can go to Proses or Batal
                if ($newStatus === self::STATUS_PROSES || $newStatus === self::STATUS_BATAL) {
                    return ['allowed' => true, 'message' => ''];
                }
                return ['allowed' => false, 'message' => 'Status "dijadwalkan" can only transition to "proses" or "batal".'];

            case self::STATUS_PROSES:
                // Proses can go to Batal or Selesai
                if ($newStatus === self::STATUS_BATAL) {
                    // Proses to Batal requires owner approval
                    if (!$user || $user->role !== 'owner') {
                        return ['allowed' => false, 'message' => 'Only owner can change status from "proses" to "batal".'];
                    }
                    if (!Hash::check($ownerPassword, $user->password)) {
                        return ['allowed' => false, 'message' => 'Owner password incorrect.'];
                    }
                    return ['allowed' => true, 'message' => ''];
                } elseif ($newStatus === self::STATUS_SELESAI) {
                    return ['allowed' => true, 'message' => ''];
                }
                return ['allowed' => false, 'message' => 'Status "proses" can only transition to "batal" or "selesai".'];

            case self::STATUS_INVOICED:
                // Invoiced can only go to Selesai (if not already selesai)
                if ($newStatus === self::STATUS_SELESAI) {
                    return ['allowed' => true, 'message' => ''];
                }
                return ['allowed' => false, 'message' => 'Status "invoiced" can only transition to "selesai".'];

            case self::STATUS_BATAL:
                // Batal cannot change to any other status
                return ['allowed' => false, 'message' => 'Status "batal" cannot be changed.'];

            case self::STATUS_SELESAI:
                // Selesai cannot change to any other status
                return ['allowed' => false, 'message' => 'Status "selesai" cannot be changed.'];

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
        return $this->belongsToMany(Staff::class, 'service_order_staff');
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
        return $this->hasOne(Invoice::class);
    }

    /**
     * Mendefinisikan relasi "hasOne" ke Invoice.
     * Satu ServiceOrder hanya memiliki satu Invoice.
     */
    public function workPhotos() // <-- TAMBAHKAN METHOD INI
    {
        return $this->hasMany(WorkPhoto::class);
    }

    
}