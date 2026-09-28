<?php

namespace App\Models;

use App\Services\WhatsAppService;
use Illuminate\Database\Eloquent\Builder;
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
    public function scopeApproved(Builder $query): Builder
    {
        return $query->whereRaw('UPPER(status) = ?', ['APPROVED']);
    }

    public static function resolveApproved(string $name, int $userId): ?self
    {
        return static::availableForUser($userId)
            ->where('name', $name)
            ->first();
    }

    /**
     * Base query returning all templates visible to the user for selection UI.
     * Shared testing templates are excluded once the user has their own sender.
     */
    public static function availableForUser(int $userId): Builder
    {
        return static::query()
            ->approved()
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId);
                if (! WhatsAppConfig::hasOwnConfig($userId)) {
                    $query->orWhereIn('name', self::SHARED_TESTING_TEMPLATES);
                }
            });
    }

    /**
     * Approved templates for a business, syncing from Meta when requested (admin send flow).
     *
     * @return array<int, string> id => name
     */
    public static function selectOptionsForUser(int $userId, bool $allowMetaSync = false): array
    {
        if ($allowMetaSync && static::availableForUser($userId)->count() === 0) {
            static::syncApprovedFromMetaForUser($userId);
        }

        return static::availableForUser($userId)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * Pull APPROVED templates from Meta for this user's WABA into the local registry.
     */
    public static function syncApprovedFromMetaForUser(int $userId): void
    {
        $config = WhatsAppConfig::forUser($userId);
        if (! $config || ! $config->isComplete()) {
            return;
        }

        $service = new WhatsAppService(
            $config->phone_number_id,
            $config->access_token,
            $config->business_account_id,
        );

        $result = $service->listMessageTemplates();
        if (isset($result['error'])) {
            return;
        }

        foreach ($result['data'] ?? [] as $item) {
            if (strtoupper((string) ($item['status'] ?? '')) !== 'APPROVED') {
                continue;
            }

            $bodyText = static::extractBodyTextFromMetaComponents($item['components'] ?? []);
            $format   = static::inferParameterFormat($bodyText);

            static::updateOrCreate(
                [
                    'user_id' => $userId,
                    'name'    => $item['name'],
                ],
                [
                    'category'             => strtoupper((string) ($item['category'] ?? 'UTILITY')),
                    'language'             => $item['language'] ?? 'en_US',
                    'body_text'            => $bodyText,
                    'parameter_format'     => $format,
                    'status'               => 'APPROVED',
                    'whatsapp_template_id' => $item['id'] ?? null,
                ]
            );
        }
    }

    /** @param  array<int, array<string, mixed>>  $components */
    public static function extractBodyTextFromMetaComponents(array $components): string
    {
        foreach ($components as $component) {
            if (strtoupper((string) ($component['type'] ?? '')) === 'BODY') {
                return (string) ($component['text'] ?? '');
            }
        }

        return '';
    }

    public static function inferParameterFormat(string $bodyText): string
    {
        preg_match_all('/\{\{([^}]+)\}\}/', $bodyText, $matches);
        foreach ($matches[1] ?? [] as $param) {
            if (! ctype_digit((string) $param)) {
                return 'named';
            }
        }

        return 'positional';
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
