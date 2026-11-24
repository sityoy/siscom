<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agreement extends Model
{
    public const CLAUSES = [
        'provider_obligations' => 'Hak dan Kewajiban Pihak Pertama',
        'client_obligations' => 'Hak dan Kewajiban Pihak Kedua',
        'support' => 'Dukungan Teknis',
        'confidentiality' => 'Kerahasiaan dan Data',
        'disputes' => 'Penyelesaian Perselisihan',
    ];

    protected $fillable = [
        'invoice_reference', 'invoice_date', 'invoice_total', 'provider_name', 'letterhead', 'client_name',
        'title', 'document_date', 'starts_on', 'ends_on', 'city', 'scope', 'payment_terms',
        'additional_terms', 'provider_signer', 'provider_title', 'client_signer', 'client_title', 'client_id', 'details',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'invoice_total' => 'decimal:2',
        'letterhead' => 'array',
        'details' => 'array',
        'document_date' => 'date',
        'starts_on' => 'date',
        'ends_on' => 'date',
        'finalized_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function scopeVisibleToClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId)->whereNotNull('finalized_at');
    }

    public function articles(): array
    {
        if (! $this->details) {
            return array_filter([
                'Ruang Lingkup' => $this->scope,
                'Ketentuan Pembayaran' => $this->payment_terms,
                'Ketentuan Tambahan' => $this->additional_terms,
            ], 'filled');
        }

        $count = $this->details['student_count'] ?? null;
        $rate = $this->details['fee_per_student'] ?? null;
        $cost = $count && $rate
            ? sprintf('%s siswa × Rp%s per siswa untuk satu periode ujian = Rp%s per periode.',
                number_format($count, 0, ',', '.'), number_format($rate, 0, ',', '.'), number_format($count * $rate, 0, ',', '.'))
            : 'Nilai bruto referensi mengikuti invoice sumber.';

        return array_filter([
            'Ruang Lingkup' => $this->scope,
            'Biaya Kerja Sama' => $cost,
            'Ketentuan Pembayaran' => $this->payment_terms,
            self::CLAUSES['provider_obligations'] => $this->details['provider_obligations'] ?? null,
            self::CLAUSES['client_obligations'] => $this->details['client_obligations'] ?? null,
            self::CLAUSES['support'] => $this->details['support'] ?? null,
            self::CLAUSES['confidentiality'] => $this->details['confidentiality'] ?? null,
            'Masa Berlaku' => $this->starts_on->translatedFormat('d F Y').' sampai dengan '.$this->ends_on->translatedFormat('d F Y').'.',
            self::CLAUSES['disputes'] => $this->details['disputes'] ?? null,
            'Ketentuan Tambahan' => $this->additional_terms,
        ], 'filled');
    }

    public function getNumberAttribute(): ?string
    {
        return $this->official_number;
    }
}
