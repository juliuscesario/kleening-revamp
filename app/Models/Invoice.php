<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\AreaScope; // <-- INI YANG BENAR

class Invoice extends Model
{
    use HasFactory;

    const STATUS_NEW = 'new';
    const STATUS_SENT = 'sent';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    protected $attributes = [
        'status' => self::STATUS_SENT,
    ];

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
        'discount',
        'discount_type',
        'transport_fee',
        'grand_total',
        'dp_type',
        'dp_value',
        'total_after_dp',
        'paid_amount',
        'status',
        'notes',
        'signature',
        'reissued_from',
    ];

    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function reissueOrigin()
    {
        return $this->belongsTo(Invoice::class, 'reissued_from');
    }

    public function reissuedInvoice()
    {
        return $this->hasOne(Invoice::class, 'reissued_from');
    }

    /**
     * Generate the next invoice number in format INV/YYYY/MM/NNN.
     * The sequence resets to 001 each new month.
     * Excludes cancelled invoices from the count.
     */
    public static function generateNumber(): string
    {
        $year  = now()->format('Y');
        $month = now()->format('m');

        // Count non-cancelled invoices this month to get the next sequence
        // Use withoutGlobalScopes() to bypass AreaScope so sequence is global
        $count = static::withoutGlobalScopes()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('status', '!=', static::STATUS_CANCELLED)
            ->count();

        $sequence = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        $candidate = "INV/{$year}/{$month}/{$sequence}";

        // Guarantee uniqueness in case of race condition or manual entries
        while (static::withoutGlobalScopes()->where('invoice_number', $candidate)->exists()) {
            $count++;
            $sequence = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
            $candidate = "INV/{$year}/{$month}/{$sequence}";
        }

        return $candidate;
    }
}
