<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [

        'client_id',

        'project_id',

        'invoice_number',

        'subtotal',

        'vat',

        'service_fee',

        'grand_total',

        'due_date',

        'status',

        'notes',

        'cashback',
    ];

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
