<?php

namespace W88\CrudSystem\Commands;


use Illuminate\Console\Command;
use W88\CrudSystem\Services\CrudGeneratorService;
use Illuminate\Support\Str;

class GenerateCrud extends Command
{
    protected $signature = 'crud:generate {name?} {--module=} {--force}';
    protected $description = 'Generate CRUD operations for a given model within a specified module based on config file';

    
    public function handle()
    {
        $force = $this->option('force');
        if ($force) {
            $sureForce = $this->confirm('Are you sure you want to delete the existing files?');
            if (!$sureForce) {
                $this->alert('No CRUD generated.');
                return;
            }
        }
        $moduleName = $this->option('module') ?? null;
        $crudName = $this->argument('name') ? strtolower(Str::snake($this->argument('name'))) : null;
        if ($crudName && !$moduleName) {
            $this->print('error', 'Module name is required when generating a single CRUD.');
            return;
        }
        $crudGeneratorService = new CrudGeneratorService();
        $generated = $crudGeneratorService->generate($moduleName, $crudName, $force);
        if ($generated == 'single') {
            $this->print('info', "CRUD for {$crudName} in module {$moduleName} generated successfully.");
        } else if ($generated == 'all') {
            $this->print('info', "All CRUD generated successfully.");
        } else {
            $this->print('warn', 'Not Found CRUD to generate.');
        }
    }

    private function print(string $type, string $message): void
    {
        $this->$type("\n\n   {$message}\n");
    }
    
}

