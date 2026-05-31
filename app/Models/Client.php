<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [

        'name',

        'company',

        'email',

        'phone',

        'address',

    ];

    public function projects()
        {
            return $this->hasMany(Project::class);
        }

    public function invoices()
        {
            return $this->hasMany(Invoice::class);
        }

     public function user()
        {
            return $this->belongsTo(User::class);
        }

    public function notifications()
        {
            return $this->hasMany(Notification::class);
        }

    public function tickets()
        {
            return $this->hasMany(Ticket::class);
        }
}
