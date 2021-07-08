<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemindMeWhen extends Model
{
    use HasFactory;

    protected $table = 'remind_me_when';
    protected $fillable = [
        'name',
        'days',        
    ];


}
