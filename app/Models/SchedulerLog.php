<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchedulerLog extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'command',
        'start_time',
        'end_time',
        'items_processed',
        'tenant_id',
    ];
}