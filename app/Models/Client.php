<?php

namespace App\Models;
use Carbon\Carbon;


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
                return 'active';
            }

            $today = Carbon::today();

            $endDate = Carbon::parse(
                $this->subscription_end
            );

            $graceDate = $endDate->copy()
                ->addDays(
                    $this->grace_period_days ?? 7
                );

            if ($today->lte($endDate)) {
                return 'active';
            }

            if ($today->lte($graceDate)) {
                return 'grace';
            }

            return 'expired';
        }
}
