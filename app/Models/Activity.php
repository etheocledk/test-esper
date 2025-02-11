<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;

class Activity extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'message', 
        'intitule', 
        'lien_hypertexte', 
        'icone',
    ];
}
