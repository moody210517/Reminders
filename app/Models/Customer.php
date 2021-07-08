<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';
    protected $fillable = [
        'company_name',
        'physical_address',
        'tel',
        'contact_person_1',
        'contact_person_1_email',
        'contact_person_1_number',
        'contact_person_2',
        'contact_person_2_email',
        'contact_person_2_number',
    ];

}
