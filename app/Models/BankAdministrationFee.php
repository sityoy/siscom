<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAdministrationFee extends Model
{
    protected $fillable = ['period', 'debited_on', 'amount', 'voided_at'];

    protected $casts = [
        'debited_on' => 'date',
        'voided_at' => 'datetime',
    ];

    public function paymentBankAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentBankAccount::class);
    }
}
