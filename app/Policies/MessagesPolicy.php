<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Messages;
use Illuminate\Auth\Access\HandlesAuthorization;

class MessagesPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool   { return true; }
    public function view(User $user, Messages $messages): bool { return true; }
    public function create(User $user): bool    { return true; }
    public function update(User $user, Messages $messages): bool { return true; }
    public function delete(User $user, Messages $messages): bool { return true; }
    public function deleteAny(User $user): bool { return true; }
    public function forceDelete(User $user, Messages $messages): bool { return true; }
    public function forceDeleteAny(User $user): bool { return true; }
    public function restore(User $user, Messages $messages): bool { return true; }
    public function restoreAny(User $user): bool { return true; }
    public function replicate(User $user, Messages $messages): bool { return true; }
    public function reorder(User $user): bool   { return true; }
}
