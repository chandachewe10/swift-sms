<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Observers\UserObserver;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       
           User::observe(UserObserver::class);

        Model::unguard();
        Filament::registerNavigationGroups([
            'Messages',
            'Developers',
            'User Management'
        ]);

           
        
    }
}