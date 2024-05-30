<?php

namespace Workbench\App\Providers;

use Workbench\App\Models\User;
use Illuminate\Support\ServiceProvider;
use Workbench\App\Repositories\UserRepository;
use Workbench\App\Services\ModelServices\UserService;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind("UserService", function ($app) {
            return new UserService(new UserRepository(new User));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
