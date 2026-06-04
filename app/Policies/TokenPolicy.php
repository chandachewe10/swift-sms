<?php

namespace App\Policies;

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

class TokenPolicy
{
    public function viewAny(User $user): bool   { return true; }
    public function view(User $user, PersonalAccessToken $token): bool { return true; }
    public function create(User $user): bool    { return true; }
    public function update(User $user, PersonalAccessToken $token): bool { return true; }
    public function delete(User $user, PersonalAccessToken $token): bool { return true; }
}
