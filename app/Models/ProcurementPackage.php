<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementPackage extends Model
{
    public const TYPES = [
        'direct' => 'SPJ Dikerjakan SIS.COM',
        'third_party' => 'SPJ Rekanan/Vendor',
    ];

    public const STATUSES = [
        'preparation' => 'Persiapan',
        'documents' => 'Penyusunan Dokumen',
        'ready' => 'Siap Diajukan',
        'paid' => 'Sudah Dibayar',
        'archived' => 'Diarsipkan',
    ];

    protected $fillable = [
        'project_id',
        'package_type',
        'procurement_method',
        'status',
        'vendor_name',
        'vendor_tax_id',
        'vendor_tax_status',
        'vendor_contact',
        'vendor_address',
        'contract_value',
        'vendor_cost',
        'vendor_tax_amount',
        'operational_cost',
        'partner_name',
        'partner_fee',
        'target_fee_percent',
        'management_fee_amount',
        'income_tax_rate',
        'income_tax_amount',
        'tax_collection_method',
        'net_profit',
        'notes',
    ];

    protected $casts = [
        'contract_value' => 'decimal:2',
        'vendor_cost' => 'decimal:2',
        'vendor_tax_amount' => 'decimal:2',
        'operational_cost' => 'decimal:2',
        'partner_fee' => 'decimal:2',
        'target_fee_percent' => 'decimal:2',
        'management_fee_amount' => 'decimal:2',
        'income_tax_rate' => 'decimal:2',
        'income_tax_amount' => 'decimal:2',
        'net_profit' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function files()
    {
        return $this->hasMany(ProcurementPackageFile::class);
    }

    public function getVendorNetPaymentAttribute(): float
    {
        return max(0, (float) $this->vendor_cost - (float) $this->vendor_tax_amount);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->package_type] ?? 'Paket SPJ';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getActualFeePercentAttribute(): float
    {
        if ((float) $this->contract_value <= 0) {
            return 0;
        }

        return ((float) $this->net_profit / (float) $this->contract_value) * 100;
    }
}
