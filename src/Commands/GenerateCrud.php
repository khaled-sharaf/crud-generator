<?php

namespace W88\CrudSystem\Commands;


use Illuminate\Console\Command;
use W88\CrudSystem\Services\CrudGeneratorService;

class GenerateCrud extends Command
{
    protected $signature = 'fr:crud-generate {name?} {module?}';
    protected $description = 'Generate CRUD operations for a given model within a specified module based on config file';

    
    public function handle()
    {
        $moduleName = $this->argument('module') ?? null;
        $crudName = $this->argument('name') ? strtolower($this->argument('name')) : null;
        $crudGeneratorService = new CrudGeneratorService();
        $crudGeneratorService->generate($moduleName, $crudName);
        if ($moduleName && $crudName) {
            $this->info("CRUD for {$crudName} in module {$moduleName} generated successfully.");
        } else {
            $this->info("CRUD generated successfully.");
        }
    }

    
}

