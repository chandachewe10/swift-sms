<?php
namespace App\Policies;

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

class TokenPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view_any_token');
    }

    public function view(User $user, PersonalAccessToken $token)
    {
        return $user->hasPermissionTo('view_token');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create_token');
    }

    public function update(User $user, PersonalAccessToken $token)
    {
        return $user->hasPermissionTo('update_token');
    }

    public function delete(User $user, PersonalAccessToken $token)
    {
        return $user->hasPermissionTo('delete_token');
    }
}
