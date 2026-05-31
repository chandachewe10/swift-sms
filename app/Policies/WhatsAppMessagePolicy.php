<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WhatsAppMessage;
use Illuminate\Auth\Access\HandlesAuthorization;

class WhatsAppMessagePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_whats::app::message');
    }

    public function view(User $user, WhatsAppMessage $message): bool
    {
        return $user->can('view_whats::app::message');
    }

    public function create(User $user): bool
    {
        return $user->can('create_whats::app::message');
    }

    public function update(User $user, WhatsAppMessage $message): bool
    {
        return $user->can('update_whats::app::message');
    }

    public function delete(User $user, WhatsAppMessage $message): bool
    {
        return $user->can('delete_whats::app::message');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_whats::app::message');
    }

    public function forceDelete(User $user, WhatsAppMessage $message): bool
    {
        return $user->can('force_delete_whats::app::message');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_whats::app::message');
    }

    public function restore(User $user, WhatsAppMessage $message): bool
    {
        return $user->can('restore_whats::app::message');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_whats::app::message');
    }

    public function replicate(User $user, WhatsAppMessage $message): bool
    {
        return $user->can('replicate_whats::app::message');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_whats::app::message');
    }
}
