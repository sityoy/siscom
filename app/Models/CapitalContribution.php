<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapitalContribution extends Model
{
    protected $fillable = [
        'contribution_date', 'source', 'amount', 'notes', 'created_by', 'invoice_id', 'invoice_reference',
        'recipient', 'transferred_on', 'transfer_reference',
    ];

    protected $casts = [
        'contribution_date' => 'date',
        'transferred_on' => 'date',
        'amount' => 'decimal:2',
        'voided_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
