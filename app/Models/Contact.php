<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;

class Contact extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = ['fullname', 'email', 'company_id'];
}
