<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WhatsAppConfig;
use Illuminate\Auth\Access\HandlesAuthorization;

class WhatsAppConfigPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_whats::app::config');
    }

    public function view(User $user, WhatsAppConfig $config): bool
    {
        return $user->can('view_whats::app::config');
    }

    public function create(User $user): bool
    {
        return $user->can('create_whats::app::config');
    }

    public function update(User $user, WhatsAppConfig $config): bool
    {
        return $user->can('update_whats::app::config');
    }

    public function delete(User $user, WhatsAppConfig $config): bool
    {
        return $user->can('delete_whats::app::config');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_whats::app::config');
    }

    public function forceDelete(User $user, WhatsAppConfig $config): bool
    {
        return $user->can('force_delete_whats::app::config');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_whats::app::config');
    }

    public function restore(User $user, WhatsAppConfig $config): bool
    {
        return $user->can('restore_whats::app::config');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_whats::app::config');
    }

    public function replicate(User $user, WhatsAppConfig $config): bool
    {
        return $user->can('replicate_whats::app::config');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_whats::app::config');
    }
}
