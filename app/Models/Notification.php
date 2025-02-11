<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;

class Notification extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'company_id',
        'notifications_by_email',
        'update_reminder_notifications',
        'new_projects_notifications_by_email',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
