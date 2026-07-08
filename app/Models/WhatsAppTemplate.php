<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppTemplate extends Model
{
    protected $table = 'whatsapp_templates';

    /** Templates available to all users for free testing. */
    public const SHARED_TESTING_TEMPLATES = [
        'opening_our_business_time',
        'system_maintenance',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'category',
        'language',
        'body_text',
        'parameter_format',
        'status',
        'whatsapp_template_id',
    ];

    /**
     * Extract parameter names/positions from the body text.
     * Named:      {{first_name}} → ['first_name', 'order_number']
     * Positional: {{1}}, {{2}}   → ['1', '2']
     */
    public function extractParams(): array
    {
        preg_match_all('/\{\{([^}]+)\}\}/', $this->body_text ?? '', $matches);
        return array_unique($matches[1] ?? []);
    }

    public function hasParams(): bool
    {
        return count($this->extractParams()) > 0;
    }

    public static function isSharedTestingTemplate(string $name): bool
    {
        return in_array($name, self::SHARED_TESTING_TEMPLATES, true);
    }

    /**
     * Return an approved template visible to the user.
     *
     * Shared testing templates are only included when the user has no own
     * WhatsApp config (i.e. they are using the admin sender for testing).
     * Once a user has their own registered number the testing templates are
     * excluded — they were created on a different WABA and will not work.
     */
    public static function resolveApproved(string $name, int $userId): ?self
    {
        return static::query()
            ->where('name', $name)
            ->where('status', 'APPROVED')
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId);
                if (! WhatsAppConfig::hasOwnConfig($userId)) {
                    $query->orWhereIn('name', self::SHARED_TESTING_TEMPLATES);
                }
            })
            ->first();
    }

    /**
     * Base query returning all templates visible to the user for selection UI.
     * Shared testing templates are excluded once the user has their own sender.
     */
    public static function availableForUser(int $userId): \Illuminate\Database\Eloquent\Builder
    {
        return static::query()
            ->where('status', 'APPROVED')
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId);
                if (! WhatsAppConfig::hasOwnConfig($userId)) {
                    $query->orWhereIn('name', self::SHARED_TESTING_TEMPLATES);
                }
            });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class);
    }
}
