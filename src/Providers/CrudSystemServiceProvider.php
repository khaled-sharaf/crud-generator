<?php

namespace W88\CrudSystem\Providers;

use Illuminate\Support\ServiceProvider;

class CrudSystemServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register any services or bindings here
    }

    public function boot()
    {
        // Load routes, views, migrations, etc.
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->publishes([
            __DIR__.'/../../config/crudsystem.php' => config_path('crudsystem.php'),
        ]);
    }

    protected function registerCommands(): void
    {
        $this->commands([
            \W88\CrudSystem\Commands\GenerateCrud::class,
            \W88\CrudSystem\Commands\MakeCrud::class,
        ]);
    }
    
}
