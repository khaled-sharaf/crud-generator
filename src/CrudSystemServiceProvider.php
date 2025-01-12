<?php

namespace Khaled\CrudSystem;

use Illuminate\Support\ServiceProvider;

class CrudSystemServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register any services or bindings here
    }

    public function boot()
    {
        $this->registerCommands();
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    protected function registerCommands(): void
    {
        $this->commands([
            \Khaled\CrudSystem\Commands\GenerateCrud::class,
            \Khaled\CrudSystem\Commands\MakeCrud::class,
        ]);
    }
    
}
