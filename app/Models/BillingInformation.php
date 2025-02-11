<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;

class BillingInformation extends Model
{
    use HasFactory, GeneratesUuid;

    protected $table = 'billing_informations';

    protected $fillable = [
        'company_id',
        'bank_name',
        'iban',
        'bic_swift',
        'account_number'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
}
