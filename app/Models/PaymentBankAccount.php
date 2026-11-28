<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentBankAccount extends Model
{
    protected $fillable = [
        'bank_name', 'account_number', 'account_holder', 'is_active',
        'product_label', 'monthly_admin_fee', 'minimum_balance',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function administrationFees(): HasMany
    {
        return $this->hasMany(BankAdministrationFee::class);
    }
}
