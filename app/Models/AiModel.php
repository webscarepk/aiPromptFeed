<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiModel extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'api_key', 'image',
        'api_endpoint', 'webhook_secret', 
        'cost_per_use', 'is_active', 'capabilities'
    ];

    protected $casts = [
        'capabilities' => 'array',
        'is_active' => 'boolean',
    ];

    public function aiPrompts()
    {
        return $this->hasMany(AiPrompt::class);
    }

    public function planCreditAllocations()
    {
        return $this->hasMany(PlanCreditAllocation::class, 'model_id');
    }

    public function userCreditBalances()
    {
        return $this->hasMany(UserCreditBalance::class, 'model_id');
    }

    public function generationJobs()
    {
        return $this->hasMany(GenerationJob::class, 'model_id');
    }
}
