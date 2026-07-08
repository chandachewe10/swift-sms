<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppConfig extends Model
{
    protected $table = 'whatsapp_configs';

    protected $fillable = [
        'user_id',
        'phone_number_id',
        'phone_number',
        'business_account_id',
        'waba_id',
        'business_id',
        'access_token',
        'app_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function forUser(int $userId): ?self
    {
        return static::where('user_id', $userId)->first();
    }

    public static function admin(): ?self
    {
        return static::query()
            ->whereHas('user.roles', fn ($query) => $query->where('name', 'super_admin'))
            ->orderBy('id')
            ->first();
    }

    /**
     * @return array{config: ?self, using_admin: bool}
     */
    public static function resolveForSending(int $userId): array
    {
        $own = static::forUser($userId);

        // Only use the user's own config when it is fully set up; a partial row
        // written by the webhook before the browser callback completes will have
        // an empty phone_number_id and must not be used for sending.
        if ($own && ! empty($own->phone_number_id)) {
            return ['config' => $own, 'using_admin' => false];
        }

        $admin = static::admin();

        return ['config' => $admin, 'using_admin' => $admin !== null];
    }
}
