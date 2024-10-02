<?php

namespace W88\CrudSystem\Commands;


use Illuminate\Console\Command;
use W88\CrudSystem\Services\CrudGeneratorService;
use Illuminate\Support\Str;

class GenerateCrud extends Command
{
    protected $signature = 'fr:crud-generate {name?} {module?} {--force}';
    protected $description = 'Generate CRUD operations for a given model within a specified module based on config file';

    
    public function handle()
    {
        $force = $this->option('force');
        if ($force) {
            $sureForce = $this->confirm('Are you sure you want to delete the existing files?');
            if (!$sureForce) {
                $this->info("No CRUD generated.");
                return;
            }
        }
        $moduleName = $this->argument('module') ?? null;
        $crudName = $this->argument('name') ? strtolower(Str::snake($this->argument('name'))) : null;
        $crudGeneratorService = new CrudGeneratorService();
        $generated = $crudGeneratorService->generate($moduleName, $crudName, $force);
        if ($generated == 'single') {
            $this->info("CRUD for {$crudName} in module {$moduleName} generated successfully.");
        } else if ($generated == 'all') {
            $this->info("All CRUD generated successfully.");
        } else {
            $this->info("No CRUD generated.");
        }
    }

    
}

