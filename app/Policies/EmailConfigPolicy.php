<?php

namespace App\Policies;

use App\Models\User;
use App\Models\EmailConfig;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmailConfigPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool   { return true; }
    public function view(User $user, EmailConfig $emailConfig): bool { return true; }
    public function create(User $user): bool    { return true; }
    public function update(User $user, EmailConfig $emailConfig): bool { return true; }
    public function delete(User $user, EmailConfig $emailConfig): bool { return true; }
    public function deleteAny(User $user): bool { return true; }
    public function forceDelete(User $user, EmailConfig $emailConfig): bool { return true; }
    public function forceDeleteAny(User $user): bool { return true; }
    public function restore(User $user, EmailConfig $emailConfig): bool { return true; }
    public function restoreAny(User $user): bool { return true; }
    public function replicate(User $user, EmailConfig $emailConfig): bool { return true; }
    public function reorder(User $user): bool   { return true; }
}
