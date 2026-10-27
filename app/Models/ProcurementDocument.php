<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementDocument extends Model
{
    public const TYPES = [
        'quotation' => 'Surat Penawaran',
        'negotiation_minutes' => 'Berita Acara Negosiasi',
        'purchase_order' => 'Surat Pesanan / SPK',
        'work_order' => 'Surat Perintah Tugas (SPT)',
        'work_report' => 'Laporan Pelaksanaan Pekerjaan',
        'handover' => 'Berita Acara Serah Terima (BAST)',
        'payment_request' => 'Surat Permohonan Pembayaran',
        'receipt' => 'Kwitansi',
    ];

    public const TYPE_CODES = [
        'quotation' => 'SPH',
        'negotiation_minutes' => 'BAN',
        'purchase_order' => 'SPK',
        'work_order' => 'SPT',
        'work_report' => 'LPP',
        'handover' => 'BAST',
        'payment_request' => 'SPP',
        'receipt' => 'KWT',
    ];

    public const STATUSES = [
        'draft' => 'Draft',
        'awaiting_signature' => 'Menunggu Tanda Tangan',
        'signed' => 'Sudah Ditandatangani',
        'paid' => 'Sudah Dibayar',
        'archived' => 'Diarsipkan',
    ];

    protected $fillable = [
        'project_id',
        'invoice_id',
        'created_by',
        'type',
        'document_number',
        'document_date',
        'status',
        'title',
        'subject',
        'amount',
        'work_start_date',
        'work_end_date',
        'school_signer_name',
        'school_signer_title',
        'provider_signer_name',
        'provider_signer_title',
        'funding_source',
        'transaction_method',
        'transaction_type',
        'provider_tax_status',
        'tax_base',
        'vat_amount',
        'income_tax_amount',
        'terms',
        'notes',
        'signed_file_path',
        'metadata',
    ];

    protected $casts = [
        'document_date' => 'date',
        'work_start_date' => 'date',
        'work_end_date' => 'date',
        'amount' => 'decimal:2',
        'tax_base' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'income_tax_amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function assignAutomaticNumber(): void
    {
        if (!$this->exists || !str_starts_with($this->document_number, 'TEMP-')) {
            return;
        }

        $date = $this->document_date ?? now();
        $code = self::TYPE_CODES[$this->type] ?? 'DOC';
        $month = self::romanMonth((int) $date->format('n'));

        $this->forceFill([
            'document_number' => sprintf(
                '%03d/%s/SIS/%s/%s',
                $this->id,
                $code,
                $month,
                $date->format('Y'),
            ),
        ])->saveQuietly();
    }

    private static function romanMonth(int $month): string
    {
        return [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ][$month];
    }
}
