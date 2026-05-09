<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'price_usd_cents',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'subscription_id');
    }

    public function creditAllocations()
    {
        return $this->hasMany(PlanCreditAllocation::class, 'plan_id');
    }
}