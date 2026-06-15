<?php

namespace App\Providers;

use App\Models\Reservation;
use App\Models\User;
use App\Observers\ReservationObserver;
use App\Policies\TeamPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Reservation::observe(ReservationObserver::class);
        Gate::policy(User::class, TeamPolicy::class);
    }
}
