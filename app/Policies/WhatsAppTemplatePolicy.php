<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WhatsAppTemplate;
use Illuminate\Auth\Access\HandlesAuthorization;

class WhatsAppTemplatePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool   { return true; }
    public function view(User $user, WhatsAppTemplate $whatsAppTemplate): bool { return true; }
    public function create(User $user): bool    { return true; }
    public function update(User $user, WhatsAppTemplate $whatsAppTemplate): bool { return true; }
    public function delete(User $user, WhatsAppTemplate $whatsAppTemplate): bool { return true; }
    public function deleteAny(User $user): bool { return true; }
    public function forceDelete(User $user, WhatsAppTemplate $whatsAppTemplate): bool { return true; }
    public function forceDeleteAny(User $user): bool { return true; }
    public function restore(User $user, WhatsAppTemplate $whatsAppTemplate): bool { return true; }
    public function restoreAny(User $user): bool { return true; }
    public function replicate(User $user, WhatsAppTemplate $whatsAppTemplate): bool { return true; }
    public function reorder(User $user): bool   { return true; }
}
