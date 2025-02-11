<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;

class Campaign extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'user_id', 'status', 'start_date', 'end_date', 'amount', 'company_id'
    ];
}
