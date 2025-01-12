<?php

namespace Khaled\CrudSystem\Commands;


use Illuminate\Console\Command;
use Khaled\CrudSystem\Services\CrudGeneratorService;
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
        $crudGeneratorService = new CrudGeneratorService($this);
        $crudGeneratorService->generate($moduleName, $crudName, $force);
    }

    private function print(string $type, string $message): void
    {
        $this->$type("\n\n  {$message}\n");
        $this->newLine();
    }

}

