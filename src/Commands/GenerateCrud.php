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

    private $backend_generator_types = ['model', 'migration', 'controller', 'request', 'resource', 'route'];
    private $frontend_generator_types = ['index', 'create', 'edit', 'show'];

    public function handle()
    {
        $moduleName = $this->argument('module');
        $name = $this->argument('name');
        $modelName = Str::studly($name);
        $version = config('app.api_version', 'v1');

        $config = $this->loadConfig($moduleName, $modelName);

        $modulePath = base_path("Modules/{$moduleName}");
        $moduleNamespace = 'Modules\\' . $moduleName;

        // Generate CRUD components using the factory
        foreach (array_merge($this->backend_generator_types, $this->frontend_generator_types) as $generator_type) {
            $generators_action = in_array($generator_type, $this->backend_generator_types) ? 'backend' : 'frontend';
            $generator = CrudGeneratorFactory::create($generators_action, $generator_type, $config, $modelName, $modulePath, $moduleNamespace, $version);
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

        return File::getRequire($configPath);
    }
}

