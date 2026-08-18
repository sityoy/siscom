<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [

        'client_id',

        'title',

        'description',

        'budget',

        'monthly_billing_active',

        'monthly_fee',

        'monthly_billing_start',

        'deadline',

        'status',

        'progress',

    ];

    protected $casts = [
        'monthly_billing_active' => 'boolean',
        'monthly_fee' => 'decimal:2',
        'monthly_billing_start' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function files()
    {
        return $this->hasMany( ProjectFile::class );
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }


}
