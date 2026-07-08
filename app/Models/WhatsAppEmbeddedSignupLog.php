<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppEmbeddedSignupLog extends Model
{
    protected $table = 'whatsapp_embedded_signup_logs';

    protected $fillable = [
        'user_id',
        'step',
        'request_payload',
        'response_payload',
        'status',
        'message',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
