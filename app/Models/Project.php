<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;

class Project extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'title',
        'theme',
        'association_name',
        'city',
        'events_offered',
        'iban',
        'description',
        'image',
        'video',
        'fiscal_receipt'
    ];

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_project', 'project_id', 'company_id')
            ->withTimestamps()
            ->withPivot('is_validated');
    }

    public function companyProjects()
    {
        return $this->hasMany(CompanyProject::class);
    }
}
