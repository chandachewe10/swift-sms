<?php

namespace App\Observers;
use Haruncpi\LaravelIdGenerator\IdGenerator;

use App\Models\User;
use Spatie\Permission\Models\Role;

class UserObserver
{

    /**
     * Handle the User "creating" event.
     */

    public function creating(User $user): void
    {
        //
    }



    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $unique_identifier = $user->id;
        $user->user_id = IdGenerator::generate(['table' => 'users', 'field' => 'user_id', 'length' => 8, 'prefix' => $unique_identifier]);
        $user->save();

        // Assign the default panel_user role so new users can access the platform
        $panelUserRole = Role::firstOrCreate(
            ['name' => 'panel_user', 'guard_name' => 'web'],
        );
        $user->assignRole($panelUserRole);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
