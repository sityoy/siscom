<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [

        'invoice_id',

        'description',

        'qty',

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
}
