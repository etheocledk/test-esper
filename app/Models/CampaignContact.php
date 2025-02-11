<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;

class CampaignContact extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'contact_id',
        'company_id',
        'campaign_id',
        'has_voted',
        'project_id'
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
