<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class TemporaryUser extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'temporary_users';

    protected $fillable = [
        'name',
        'full_name',
        'email',
        'password',
        'verification_token',
        'expires_at',
    ];

    protected $hidden = [
        'password',
        'verification_token',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
?>
