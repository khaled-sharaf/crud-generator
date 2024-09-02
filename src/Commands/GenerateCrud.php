<?php

namespace W88\CrudSystem\Commands;

use W88\CrudSystem\Factories\CrudGeneratorFactory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateCrud extends Command
{
    protected $signature = 'fr:crud-generate {module} {name}';
    protected $description = 'Generate CRUD operations for a given model within a specified module based on config file';

    public function handle()
    {
        $moduleName = $this->argument('module');
        $name = $this->argument('name');
        $modelName = Str::studly($name);
        $version = env('API_VERSION', 'v1');

        $config = $this->loadConfig($moduleName, $modelName);

        $modulePath = base_path("Modules/{$moduleName}");
        $moduleNamespace = 'Modules\\' . $moduleName;

        // Generate CRUD components using the factory
        foreach (['model', 'migration', 'controller', 'request', 'resource', 'route'] as $type) {
            $generator = CrudGeneratorFactory::create($type, $config, $modelName, $modulePath, $moduleNamespace, $version);
            $generator->generate();
        }

        $this->info("CRUD for {$modelName} in module {$moduleName} generated successfully.");
    }

    protected function loadConfig($moduleName, $modelName)
    {
        $configPath = base_path("Modules/{$moduleName}/config/cruds/" . strtolower($modelName) . '.php');
        if (!File::exists($configPath)) {
            $this->error("Config file not found at path: {$configPath}");
            exit;
        }

        return include $configPath;
    }
}

