<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Contact;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool   { return true; }
    public function view(User $user, Contact $contact): bool { return true; }
    public function create(User $user): bool    { return true; }
    public function update(User $user, Contact $contact): bool { return true; }
    public function delete(User $user, Contact $contact): bool { return true; }
    public function deleteAny(User $user): bool { return true; }
    public function forceDelete(User $user, Contact $contact): bool { return true; }
    public function forceDeleteAny(User $user): bool { return true; }
    public function restore(User $user, Contact $contact): bool { return true; }
    public function restoreAny(User $user): bool { return true; }
    public function replicate(User $user, Contact $contact): bool { return true; }
    public function reorder(User $user): bool   { return true; }
}
