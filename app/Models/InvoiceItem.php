<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    public const TYPES = [
        'goods' => 'Barang',
        'service' => 'Jasa',
        'rental' => 'Sewa',
        'license' => 'Lisensi/Software',
        'other' => 'Lainnya',
    ];

    public const UNITS = [
        'Pcs', 'Unit', 'Paket', 'Set', 'Lisensi', 'Orang', 'Siswa',
        'Jam', 'Hari', 'Bulan', 'Tahun', 'Meter', 'Kg', 'Lot',
    ];

    protected $fillable = [

        'invoice_id',

        'description',

        'item_type',

        'specification',

        'qty',

        'unit',

        'price',

        'total',

        'duration',

        'duration_type',

        'start_date',

        'end_date',

    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->item_type] ?? ucfirst($this->item_type);
    }
}
