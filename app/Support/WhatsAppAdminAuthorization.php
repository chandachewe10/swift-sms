<?php

namespace App\Support;

use App\Models\User;

class WhatsAppAdminAuthorization
{
    /**
     * Resolve which user owns a WhatsApp template/message action.
     * Non–super-admins always act as themselves; target_user_id is ignored/rejected.
     *
     * @return array{user_id: int, error: ?string, message: ?string}
     */
    public static function resolveOwnerUserId(array &$data): array
    {
        $user = auth()->user();
        if (! $user) {
            return [
                'user_id' => 0,
                'error'   => 'unauthenticated',
                'message' => 'You must be signed in.',
            ];
        }

        $requested = array_key_exists('target_user_id', $data)
            ? (int) $data['target_user_id']
            : null;

        unset($data['target_user_id']);

        if ($user->isSuperAdmin()) {
            if (! $requested || $requested <= 0) {
                return [
                    'user_id' => 0,
                    'error'   => 'business_required',
                    'message' => 'Select the business you are acting for.',
                ];
            }

            $allowed = User::adminWhatsAppBusinessSelectOptions();
            if (! array_key_exists($requested, $allowed)) {
                return [
                    'user_id' => 0,
                    'error'   => 'invalid_business',
                    'message' => 'That business is not allowed for admin WhatsApp actions.',
                ];
            }

            return ['user_id' => $requested, 'error' => null, 'message' => null];
        }

        if ($requested !== null && $requested > 0 && $requested !== $user->id) {
            return [
                'user_id' => 0,
                'error'   => 'forbidden',
                'message' => 'You cannot create or send WhatsApp content for another business.',
            ];
        }

        return ['user_id' => $user->id, 'error' => null, 'message' => null];
    }
}
