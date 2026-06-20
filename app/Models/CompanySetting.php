<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [

        'company_name',

        'company_email',

        'company_phone',

        'company_address',

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
}
