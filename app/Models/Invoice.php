<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [

        'client_id',

        'project_id',

        'invoice_number',

        'subtotal',

        'vat_percent',

        'vat',

        'service_fee',

        'late_fee_active',

        'late_fee_per_month',

        'grand_total',

        'due_date',

        'status',

        'notes',

        'cashback',

        'invoice_type',

        'paid_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'late_fee_active' => 'boolean',
        'late_fee_per_month' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function calculateLateMonths($asOf = null): int
    {
        if (
            !$this->late_fee_active ||
            !$this->due_date ||
            $this->status === 'cancelled'
        ) {
            return 0;
        }

        $calculationDate = $asOf
            ? Carbon::parse($asOf)->startOfDay()
            : ($this->status === 'paid' && $this->paid_at
                ? $this->paid_at->copy()->startOfDay()
                : Carbon::today());

        $dueDate = $this->due_date->copy()->startOfDay();

        if ($calculationDate->lte($dueDate)) {
            return 0;
        }

        $daysLate = $dueDate->diffInDays($calculationDate);

        return (int) ceil($daysLate / 30);
    }

    public function calculateLateFee($asOf = null): float
    {
        return $this->calculateLateMonths($asOf)
            * (float) ($this->late_fee_per_month ?? 0);
    }

    public function calculateTotalDue($asOf = null): float
    {
        return (float) $this->grand_total
            + $this->calculateLateFee($asOf);
    }

    public function getLateMonthsAttribute(): int
    {
        return $this->calculateLateMonths();
    }

    public function getLateFeeAmountAttribute(): float
    {
        return $this->calculateLateFee();
    }

    public function getTotalDueAttribute(): float
    {
        return $this->calculateTotalDue();
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function items()
    {
        return $this->hasMany(
            InvoiceItem::class
        );
    }
}
