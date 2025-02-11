<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\GeneratesUuid;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, GeneratesUuid;

    protected $fillable = [
        'firstname', 'lastname', 'email', 'tel', 'country', 'address', 'city', 'password', 'avatar'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

}