<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OrderSession extends Model
{
    protected $fillable = [
        'service_order_id',
        'session_number',
        'tanggal',
        'jam',
        'type',
        'status',
        'notes',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(Staff::class, 'order_session_staff')
                    ->withPivot('signature_image')
                    ->withTimestamps();
    }

    /**
     * Get human-readable type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'kerja'    => 'Kerja',
            'pickup'   => 'Pickup',
            'delivery' => 'Delivery',
            'survey'   => 'Survey',
            'workshop' => 'Workshop',
            'rework'   => 'Rework',
            default    => ucfirst($this->type),
        };
    }

    /**
     * Get human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'booked' => 'Booked',
            'proses' => 'Proses',
            'done'   => 'Done',
            'cancel' => 'Cancel',
            default  => ucfirst($this->status),
        };
    }
}
