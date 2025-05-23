<?php

namespace Khaled\CrudSystem\Services;

use Illuminate\Support\Facades\File;
use Khaled\CrudSystem\Facades\Crud;
use Illuminate\Support\Str;
use Khaled\CrudSystem\Models\Crud as ModelsCrud;
use Illuminate\Console\Command;

class CrudGeneratorService
{

    public function __construct(
        private ?Command $command = null
    ) {}


    public function generate(string $moduleName = null, string $crudName = null, bool $force = false)
    {
        Crud::formatCommandInfo($this->command, "Generating CRUD");
        $generated = null;
        if ($moduleName && $crudName) {
            $crud = $this->getCrud($moduleName, $crudName, $force);
            if ($crud) {
                $this->singleGenerator($crud);
                $generated = 'single';
            }
        } else {
            foreach ($this->getAllCruds($moduleName) as $crud) {
                $this->singleGenerator($crud);
                $generated = 'all';
            }
        }
        $this->command->newLine();
        if ($generated == 'single') {
            $this->command->info("  CRUD [{$crudName}] in module [{$moduleName}] has been successfully generated.");
        } else if ($generated == 'all') {
            $this->command->info("  All CRUD generated successfully.");
        } else {
            $this->command->warn('  Not Found CRUD to generate.');
        }
        $this->command->newLine();
    }

    protected function singleGenerator($crud)
    {
        $this->command->line(Crud::formatCommandRunGenerator($crud->file_name, 'running'));
        $time = microtime(true) * 1000;
        $moduleName = $crud->module;
        $config = $this->loadCrudClientConfig($moduleName, $crud->file_name);
        if ($config === false) {
            $this->command->line(Crud::formatCommandRunGenerator($crud->file_name, 'done', 'NOT FOUND CONFIG'));
            return;
        }
        $this->runAllGenerators($moduleName, $config);
        $crud->markAsGenerated();
        $time = (int) ((microtime(true) * 1000) - $time);
        $this->command->line(Crud::formatCommandRunGenerator($crud->file_name, 'done', 'DONE', $time));
        $this->command->newLine();
        sleep(1);
    }

    protected function runAllGenerators(string $moduleName, array $config): void
    {
        $version = strtolower(config('app.api_version', 'v1'));
        foreach ($this->getGenerators() as $fileName => $generators) {
            foreach ($generators as $generatorType) {
                $configData = [
                    'config' => $config,
                    'modelName' => Str::studly($config['name']),
                    'moduleName' => $moduleName,
                    'frontendModule' => $config['frontendModule'] ?? $moduleName,
                    'version' => $version,
                ];
                $generatorClass = $this->getGeneratorClass($fileName, $generatorType);
                $generator = new $generatorClass($configData);
                if (!$generator->checkBeforeGenerate()) continue;
                $generator->generate();
            }
        }
    }

    protected function getGenerators()
    {
        return Crud::config('generator.generators');
    }

    protected function getGeneratorClass($fileName, $generatorType)
    {
        $fileName = Str::studly($fileName);
        $generatorType = Str::studly($generatorType) . 'Generator';
        $className = "Khaled\CrudSystem\Generators\\{$fileName}\\{$generatorType}";
        if (!class_exists($className)) {
            throw new \Exception('Unknown generator type: ' . $generatorType);
        }
        return $className;
    }

    protected function loadCrudClientConfig($moduleName, $crudFileName)
    {
        $configPath = base_path("Modules/{$moduleName}/config/cruds/" . $crudFileName . '.php');
        if (!File::exists($configPath)) {
            // throw new \Exception('Config not found for ' . $moduleName . ' ' . $crudFileName);
            return false;
        }
        return File::getRequire($configPath);
    }

    protected function getCrud(string $moduleName, string $crudName, bool $force = false)
    {
        $crud = ModelsCrud::where('module', $moduleName)->where('name', $crudName);
        if ($force === false) {
            $crud->generated(false);
        }
        return $crud->first();
    }

    protected function getAllCruds(string $moduleName = null)
    {
        return ModelsCrud::query()->generated(false)->when($moduleName, function ($query, $moduleName) {
            return $query->where('module', $moduleName);
        })->orderBy('created_at')->get();
    }

    public function generateCrudFromDatabase($crud, $config)
    {
        $this->runAllGenerators($crud->module, $config);
        $crud->markAsGenerated();
        return $crud;
    }

}
    