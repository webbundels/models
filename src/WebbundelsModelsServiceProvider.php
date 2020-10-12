<?php

namespace Webbundels\Models;

use Illuminate\Support\ServiceProvider;
use Webbundels\Models\Console\ModelMakeCommand;
use Webbundels\Models\Console\ResourceMakeCommand;
use Webbundels\Models\Console\RepositoryMakeCommand;
use Webbundels\Models\Console\ModelClassesMakeCommand;
use Webbundels\Models\Console\ModelServiceMakeCommand;

class WebbundelsModelsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ModelMakeCommand::class,
                ModelServiceMakeCommand::class,
                RepositoryMakeCommand::class,
                ModelClassesMakeCommand::class,
                ResourceMakeCommand::class
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
    }
}
