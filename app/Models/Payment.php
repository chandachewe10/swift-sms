<?php

namespace App\Models;

use App\Models\User;
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
        'depositId',
        'messages',
        'status',
    ];

    /**
     * The customer/company that made this payment.
     * payments.company_id → users.user_id
     */
    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id', 'user_id');
    }
}
