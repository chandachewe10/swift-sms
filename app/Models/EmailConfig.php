<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailConfig extends Model
{
    protected $table = 'email_configs';

    protected $fillable = [
        'user_id',
        'host',
        'port',
        'encryption',
        'username',
        'password',
        'from_name',
        'from_email',
    ];

    protected $casts = [
        'password' => 'encrypted',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
