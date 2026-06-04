<?php

namespace App\Policies;

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Auth\Access\HandlesAuthorization;

class PersonalAccessTokenPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool   { return true; }
    public function view(User $user, PersonalAccessToken $personalAccessToken): bool { return true; }
    public function create(User $user): bool    { return true; }
    public function update(User $user, PersonalAccessToken $personalAccessToken): bool { return true; }
    public function delete(User $user, PersonalAccessToken $personalAccessToken): bool { return true; }
    public function deleteAny(User $user): bool { return true; }
    public function forceDelete(User $user, PersonalAccessToken $personalAccessToken): bool { return true; }
    public function forceDeleteAny(User $user): bool { return true; }
    public function restore(User $user, PersonalAccessToken $personalAccessToken): bool { return true; }
    public function restoreAny(User $user): bool { return true; }
    public function replicate(User $user, PersonalAccessToken $personalAccessToken): bool { return true; }
    public function reorder(User $user): bool   { return true; }
}
