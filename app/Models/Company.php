<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\GeneratesUuid;

class Company extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, GeneratesUuid;

    protected $fillable = [
        'logo',
        'name',
        'email',
        'abonnement',
        'amount',
        'password',
        'bio',
        'code_postal',
        'phone',
        'address',
        'numerofiscal'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function members()
    {
        return $this->hasMany(CompanyMember::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'company_project', 'company_id', 'project_id')
                    ->withTimestamps() 
                    ->withPivot('is_validated'); 
    }
}
