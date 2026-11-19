<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CapitalContribution extends Model
{
    protected $fillable = ['contribution_date', 'source', 'amount', 'notes', 'created_by'];

    protected $casts = [
        'contribution_date' => 'date',
        'amount' => 'decimal:2',
        'voided_at' => 'datetime',
    ];
}
