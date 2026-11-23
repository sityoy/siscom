<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agreement extends Model
{
    protected $fillable = [
        'invoice_reference', 'invoice_date', 'invoice_total', 'provider_name', 'letterhead', 'client_name',
        'title', 'document_date', 'starts_on', 'ends_on', 'city', 'scope', 'payment_terms',
        'additional_terms', 'provider_signer', 'provider_title', 'client_signer', 'client_title',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'invoice_total' => 'decimal:2',
        'letterhead' => 'array',
        'document_date' => 'date',
        'starts_on' => 'date',
        'ends_on' => 'date',
        'finalized_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getNumberAttribute(): string
    {
        return sprintf('%04d/MOU/SIS/%s', $this->id, $this->created_at->format('Y'));
    }
}
