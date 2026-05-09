<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanCreditAllocation extends Model
{
    protected $fillable = [
        'plan_id',
        'model_id',
        'credits_granted'
    ];

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function aiModel()
    {
        return $this->belongsTo(AiModel::class, 'model_id');
    }
}