<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'full_name',
        'avatar_url',
        'subscription_id',
        'email_verified_at',
        'phone',
        'phone_verified_at',
        'streak_count',
        'last_claimed_at',
    ];

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_id');
    }

    public function creditBalances()
    {
        return $this->hasMany(UserCreditBalance::class);
    }

    public function creditHistories()
    {
        return $this->hasMany(CreditHistory::class);
    }

    public function generationJobs()
    {
        return $this->hasMany(GenerationJob::class);
    }

    public function favoritePrompts()
    {
        return $this->belongsToMany(AiPrompt::class, 'favorite_prompts')->withTimestamps();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_claimed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
