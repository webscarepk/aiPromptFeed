<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditHistory extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'description',
        'balance_before',
        'balance_after',
        'generation_job_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function generationJob()
    {
        return $this->belongsTo(GenerationJob::class);
    }
}
