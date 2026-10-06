<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsDeliveryQueue extends Model
{
    protected $table = 'sms_delivery_queue';

    protected $fillable = [
        'company_id',
        'sender_id',
        'message',
        'contacts',
        'failed_count',
        'provider_response',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'contacts' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id', 'user_id');
    }
}
