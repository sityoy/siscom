<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [

        'user_id',
        'name',
        'company',
        'email',
        'phone',
        'address',

        'package_name',
        'package_price',
        'subscription_start',
        'subscription_end',
        'grace_period_days',

    ];

    // protected $with = [

    //     'projects',

    //     'invoices'
    // ];

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

    public function getSubscriptionStatusAttribute()
        {
            if (!$this->subscription_end) {
                return 'inactive';
            }

            $today = now();

            if ($today <= $this->subscription_end) {
                return 'active';
            }

            if (
                $today <=
                \Carbon\Carbon::parse(
                    $this->subscription_end
                )->addDays(
                    $this->grace_period_days
                )
            ) {
                return 'grace';
            }

            return 'expired';
        }
}
