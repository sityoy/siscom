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

        'deadline',

        'status',

        'progress',

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
