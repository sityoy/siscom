<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceWhatsAppReminder extends Model
{
    protected $fillable = [
        'invoice_id',
        'phone',
        'reminder_date',
        'scheduled_at',
        'sent_at',
        'status',
        'response',
        'error',
    ];

    protected $casts = [
        'reminder_date' => 'date',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
