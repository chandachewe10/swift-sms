<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WhatsAppTemplate;
use Illuminate\Auth\Access\HandlesAuthorization;

class WhatsAppTemplatePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_whats::app::template');
    }

    public function view(User $user, WhatsAppTemplate $template): bool
    {
        return $user->can('view_whats::app::template');
    }

    public function create(User $user): bool
    {
        return $user->can('create_whats::app::template');
    }

    public function update(User $user, WhatsAppTemplate $template): bool
    {
        return $user->can('update_whats::app::template');
    }

    public function delete(User $user, WhatsAppTemplate $template): bool
    {
        return $user->can('delete_whats::app::template');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_whats::app::template');
    }

    public function forceDelete(User $user, WhatsAppTemplate $template): bool
    {
        return $user->can('force_delete_whats::app::template');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_whats::app::template');
    }

    public function restore(User $user, WhatsAppTemplate $template): bool
    {
        return $user->can('restore_whats::app::template');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_whats::app::template');
    }

    public function replicate(User $user, WhatsAppTemplate $template): bool
    {
        return $user->can('replicate_whats::app::template');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_whats::app::template');
    }
}
