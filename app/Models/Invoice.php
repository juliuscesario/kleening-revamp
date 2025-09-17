<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\AreaScope; // <-- INI YANG BENAR

class Invoice extends Model
{
    use HasFactory;
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new AreaScope);
    }
    protected $fillable = [
        'service_order_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'subtotal',
        'transport_fee',
        'grand_total',
        'status',
        'signature',
    ];

    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class);
    }
}