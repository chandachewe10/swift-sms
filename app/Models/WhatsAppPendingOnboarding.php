<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppPendingOnboarding extends Model
{
    protected $table = 'whatsapp_pending_onboardings';

    protected $fillable = [
        'user_id',
        'meta_business_id',
        'waba_id',
        'app_id',
        'state_token',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Find the most recent pending onboarding for a given user.
     */
    public static function forUser(int $userId): ?self
    {
        return static::where('user_id', $userId)
            ->whereIn('status', ['pending', 'webhook_received'])
            ->latest()
            ->first();
    }

    /**
     * Find a pending onboarding by the Meta Business Portfolio ID (owner_business_id).
     */
    public static function forMetaBusiness(string $metaBusinessId): ?self
    {
        return static::where('meta_business_id', $metaBusinessId)
            ->whereIn('status', ['pending', 'webhook_received'])
            ->latest()
            ->first();
    }

    public function markCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }
}
