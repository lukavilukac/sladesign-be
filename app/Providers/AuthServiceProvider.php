<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('viewLogViewer', fn (?User $user): bool => (bool) $user?->isAdmin());

        // There is no separate privileged role: logs cannot be deleted in the viewer.
        Gate::define('deleteLogFile', fn (?User $user): bool => false);
        Gate::define('deleteLogFolder', fn (?User $user): bool => false);
    }
}
