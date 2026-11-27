<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanySetting extends Model
{
    protected $fillable = [

        'company_name',

        'legal_name',

        'company_email',

        'company_phone',

        'company_address',

        'nib',
        'npwp',
        'tax_status',
        'income_tax_scheme',
        'income_tax_rate',
        'pp55_active',
        'pp55_bpe_number',
        'pp55_case_number',
        'pp55_certificate_number',
        'pp55_certificate_date',
        'pp55_certificate_document',
        'pp55_notes',
        'pp55_effective_date',
        'pp55_document',
        'director_name',
        'director_title',
        'city',

        'website',

        'logo',

        'instagram',

        'facebook',

        'linkedin',

    ];

    protected $casts = [
        'income_tax_rate' => 'decimal:2',
        'pp55_active' => 'boolean',
        'pp55_effective_date' => 'date',
        'pp55_certificate_date' => 'date',
    ];

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(PaymentBankAccount::class)->orderBy('id');
    }

    public function activeBankAccounts(): HasMany
    {
        return $this->bankAccounts()->where('is_active', true);
    }

    public function isPkp(): bool
    {
        return $this->tax_status === 'pkp';
    }

    public function usesUmkmFinal(): bool
    {
        return $this->income_tax_scheme === 'umkm_final'
            && $this->pp55_active;
    }

    public function getTaxStatusLabelAttribute(): string
    {
        return match ($this->tax_status) {
            'pkp' => 'PKP',
            'non_pkp' => 'Non-PKP',
            default => 'Belum diatur',
        };
    }
}
