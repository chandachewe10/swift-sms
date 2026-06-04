<?php

namespace App\Policies;

use App\Models\User;
use App\Models\EmailMessage;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmailMessagePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool   { return true; }
    public function view(User $user, EmailMessage $emailMessage): bool { return true; }
    public function create(User $user): bool    { return true; }
    public function update(User $user, EmailMessage $emailMessage): bool { return true; }
    public function delete(User $user, EmailMessage $emailMessage): bool { return true; }
    public function deleteAny(User $user): bool { return true; }
    public function forceDelete(User $user, EmailMessage $emailMessage): bool { return true; }
    public function forceDeleteAny(User $user): bool { return true; }
    public function restore(User $user, EmailMessage $emailMessage): bool { return true; }
    public function restoreAny(User $user): bool { return true; }
    public function replicate(User $user, EmailMessage $emailMessage): bool { return true; }
    public function reorder(User $user): bool   { return true; }
}
