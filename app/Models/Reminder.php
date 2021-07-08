<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;

    protected $table = 'reminder';
    protected $fillable = [
        'customer_id',
        'service_id',
        'detail',
        'start_date',
        'duration',
        'expiry',
        'reminder_me_via',
        'reminder_me_when',
        'reminder_customer_via',
        'created_by'
    ];

}
