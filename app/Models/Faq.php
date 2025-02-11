<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;

class Faq extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'titre',
        'reponse',
        'categorie',
        'icone',
    ];

}
