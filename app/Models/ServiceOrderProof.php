<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrderProof extends Model
{
    protected $fillable = [
        'service_order_id',
        'order_session_id',
        'staff_id',
        'type',
        'file_path',
    ];

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function orderSession(): BelongsTo
    {
        return $this->belongsTo(OrderSession::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
