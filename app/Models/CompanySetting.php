<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'pp55_effective_date',
        'pp55_document',
        'director_name',
        'director_title',
        'city',

        'bank_jakarta',
        'bank_jakarta_name',

        'bank_mandiri',
        'bank_mandiri_name',

        'bank_bca',
        'bank_bca_name',

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
    ];

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
