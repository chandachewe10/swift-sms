<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name' ,
        'company_id',
        'phone1',
        'phone2',
        'phone3',
        'email',
        'address',
        'company', 
        'nationality',       
        'tag'            
    ];
}
