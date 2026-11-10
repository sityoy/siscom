<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementPackageFile extends Model
{
    public const CATEGORIES = [
        'vendor_legality' => 'Legalitas Vendor (NIB/NPWP)',
        'vendor_quotation' => 'Penawaran Vendor',
        'vendor_invoice' => 'Invoice Vendor',
        'contract' => 'Kontrak/MoU Vendor',
        'delivery_note' => 'Surat Jalan/Bukti Serah Terima',
        'payment_proof' => 'Bukti Pembayaran Vendor',
        'tax_proof' => 'Bukti Potong/Setor Pajak',
        'other' => 'Dokumen Pendukung Lainnya',
    ];

    protected $fillable = [
        'procurement_package_id',
        'uploaded_by',
        'category',
        'title',
        'file_path',
        'original_name',
        'file_size',
    ];

    public function package()
    {
        return $this->belongsTo(ProcurementPackage::class, 'procurement_package_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? 'Dokumen Pendukung';
    }
}
