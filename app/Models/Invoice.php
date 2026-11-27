<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    public const PAYABLE_STATUSES = ['unpaid', 'partial'];

    public const TYPES = [
        'general' => 'Invoice Umum',
        'project' => 'Invoice Jasa/Sewa',
        'spj' => 'Invoice SPJ/Rekanan',
        'renewal' => 'Perpanjangan Paket',
    ];

    public const CLIENT_SIGNATURE_MODES = [
        'none' => 'Hanya pihak PT',
        'received' => 'Diterima oleh klien',
        'approved' => 'Disetujui oleh klien',
    ];

    public const COMPANY_SIGNATURE_MODES = [
        'blank' => 'Kosong untuk tanda tangan manual',
        'artwork' => 'Tanda tangan dan stempel + QR',
        'qr' => 'QR verifikasi saja',
    ];

    protected $attributes = ['client_signature_mode' => 'none', 'company_signature_mode' => 'blank'];

    protected $hidden = ['verification_code'];

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

        'client_signature_mode',

        'company_signature_mode',
        'verification_code',

        'paid_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'vat_percent' => 'decimal:2',
        'vat' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'cashback' => 'decimal:2',
        'late_fee_active' => 'boolean',
        'late_fee_per_month' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'income_tax_rate' => 'decimal:2',
        'income_tax_base' => 'decimal:2',
        'income_tax_withheld' => 'boolean',
        'income_tax_amount' => 'decimal:2',
    ];

    public function clientSignatureMode(): string
    {
        // Existing invoices used a client approval block before this option was added.
        return $this->client_signature_mode ?? 'approved';
    }

    public function verificationUrl(): ?string
    {
        if (! $this->verification_code || $this->company_signature_mode === 'blank' || $this->status === 'cancelled') {
            return null;
        }

        return rtrim(config('app.url'), '/').route('invoice.verify', ['code' => $this->verification_code], false);
    }

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
            $this->calculateTotalDue($asOf) - ($this->income_tax_withheld ? (float) $this->income_tax_amount : 0),
        );
    }

    public function outstandingBalance(float $amountPaid, $asOf = null): float
    {
        return max(0, round($this->calculateNetReceivable($asOf) - $amountPaid, 2));
    }

    public function recordedPaymentsTotal(): float
    {
        if (array_key_exists('amount_paid', $this->attributes)) {
            return (float) $this->attributes['amount_paid'];
        }

        return $this->exists ? (float) $this->payments()->sum('amount') : 0.0;
    }

    public function canReceivePayment(float $amountPaid, $asOf = null): bool
    {
        return in_array($this->status, self::PAYABLE_STATUSES, true)
            && $this->outstandingBalance($amountPaid, $asOf) > 0;
    }

    public function paymentBlockReason(float $amountPaid): ?string
    {
        if ($this->canReceivePayment($amountPaid)) {
            return null;
        }

        return match ($this->status) {
            'paid' => 'Invoice sudah lunas.',
            'cancelled' => 'Invoice dibatalkan.',
            'unpaid', 'partial' => 'Tidak ada sisa tagihan.',
            default => 'Status invoice belum mendukung pembayaran.',
        };
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

    public function salaryAllocations(): HasMany
    {
        return $this->hasMany(SalaryAllocation::class);
    }

    public function capitalContributions(): HasMany
    {
        return $this->hasMany(CapitalContribution::class);
    }

    public function agreement(): HasOne
    {
        return $this->hasOne(Agreement::class);
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
