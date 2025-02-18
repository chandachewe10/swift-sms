<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SenderId extends Model
{
    use HasFactory;


     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'company_id',
        'is_approved',
        'company_phone'
    ];


    public function username()
    {
        
        return $this->belongsTo(User::class, 'company_id','user_id');
    }

    public function getIsApprovedAttribute($value) {
        if($value == 1){
            return 'APPROVED';
        }
        elseif($value == 2){
            return 'PENDING APPROVAL';
        }
        else{
            return 'REJECTED';
        }
        
    }
}
