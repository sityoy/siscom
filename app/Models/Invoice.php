<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    public const TYPES = [
        'general' => 'Invoice Umum',
        'project' => 'Invoice Jasa/Sewa',
        'spj' => 'Invoice SPJ/Rekanan',
        'renewal' => 'Perpanjangan Paket',
    ];

    protected $fillable = [

        'client_id',

        'project_id',

        'invoice_number',

        'invoice_date',

        'subtotal',

        'vat_percent',

        'vat',

        'provider_tax_status',

        'income_tax_scheme',

        'income_tax_rate',

        'income_tax_base',

        'income_tax_withheld',

        'income_tax_amount',

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
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'late_fee_active' => 'boolean',
        'late_fee_per_month' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'income_tax_rate' => 'decimal:2',
        'income_tax_base' => 'decimal:2',
        'income_tax_withheld' => 'boolean',
        'income_tax_amount' => 'decimal:2',
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

    public function getNetReceivableAttribute(): float
    {
        return $this->calculateNetReceivable();
    }

    public function calculateNetReceivable($asOf = null): float
    {
        return max(
            0,
            $this->calculateTotalDue($asOf) - (float) $this->income_tax_amount,
        );
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

    public function whatsappReminders()
    {
        return $this->hasMany(
            InvoiceWhatsAppReminder::class
        );
    }

    public function procurementDocuments()
    {
        return $this->hasMany(ProcurementDocument::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->invoice_type] ?? ucfirst($this->invoice_type);
    }

    public function usesServicePeriod(): bool
    {
        return in_array($this->invoice_type, ['project', 'renewal'], true);
    }
}
