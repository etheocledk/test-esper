<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;

class CompanyProject extends Model
{
    use HasFactory, GeneratesUuid;

    protected $table = 'company_project';

    protected $fillable = [
        'company_id',
        'project_id',
        'is_validated',
        'campaign_id',
        'amount'
    ];
    
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
