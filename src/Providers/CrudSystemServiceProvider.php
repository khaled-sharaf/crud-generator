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
        $this->registerCommands();
    }

    protected function registerCommands(): void
    {
        $this->commands([
            \W88\CrudSystem\Commands\GenerateCrud::class,
            \W88\CrudSystem\Commands\MakeCrud::class,
        ]);
    }
    
}
