<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCreditBalance extends Model
{
    protected $fillable = [
        'user_id',
        'model_id',
        'credits_remaining',
        'credits_total'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function aiModel()
    {
        return $this->belongsTo(AiModel::class, 'model_id');
    }
}