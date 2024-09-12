<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;



    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'merchant_reference',
        'company_id',
        'reference',
        'currency',
        'customer_wallet',
        'amount',
        'fee_amount',
        'percentage',
        'transaction_amount',

        
    ];
}
