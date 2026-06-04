<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Payment;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool   { return true; }
    public function view(User $user, Payment $payment): bool { return true; }
    public function create(User $user): bool    { return true; }
    public function update(User $user, Payment $payment): bool { return true; }
    public function delete(User $user, Payment $payment): bool { return $user->hasRole('super_admin'); }
    public function deleteAny(User $user): bool { return $user->hasRole('super_admin'); }
    public function forceDelete(User $user, Payment $payment): bool { return $user->hasRole('super_admin'); }
    public function forceDeleteAny(User $user): bool { return $user->hasRole('super_admin'); }
    public function restore(User $user, Payment $payment): bool { return true; }
    public function restoreAny(User $user): bool { return true; }
    public function replicate(User $user, Payment $payment): bool { return true; }
    public function reorder(User $user): bool   { return true; }
}
