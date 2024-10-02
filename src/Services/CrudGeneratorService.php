<?php

namespace W88\CrudSystem\Services;

use Illuminate\Support\Facades\File;
use W88\CrudSystem\Facades\Crud;
use Illuminate\Support\Str;
use W88\CrudSystem\Models\Crud as ModelsCrud;

class CrudGeneratorService
{

    public function generate(string $moduleName = null, string $crudName = null, bool $force = false)
    {
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
            }
            $generated = 'all';
        }
        return $generated;
    }

    protected function singleGenerator($crud)
    {
        $moduleName = $crud->module;
        $config = $this->loadCrudClientConfig($moduleName, $crud->file_name);
        $lockAfterGenerate = $config['lockAfterGenerate'] ?? false;
        $this->runAllGenerators($moduleName, $config);
        $crud->markAsGenerated();
        if ($lockAfterGenerate) {
            $crud->markAsLocked();
        }
    }

    protected function runAllGenerators(string $moduleName, array $config): void
    {
        $version = strtolower(config('app.api_version', 'v1'));
        foreach ($this->getGenerators() as $fileName => $generators) {
            foreach ($generators as $generatorType) {
                $configData = [
                    'moduleName' => $moduleName,
                    'config' => $config,
                    'version' => $version,
                ];
                $generatorClass = $this->getGeneratorClass($fileName, $generatorType);
                $generator = new $generatorClass($configData);
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
        $className = "W88\CrudSystem\Generators\\{$fileName}\\{$generatorType}";
        if (!class_exists($className)) {
            throw new \Exception('Unknown generator type: ' . $generatorType);
        }
        return $className;
    }

    protected function loadCrudClientConfig($moduleName, $crudFileName)
    {
        $configPath = base_path("Modules/{$moduleName}/config/cruds/" . $crudFileName . '.php');
        if (!File::exists($configPath)) {
            throw new \Exception('Config not found for ' . $moduleName . ' ' . $crudFileName);
        }
        return File::getRequire($configPath);
    }

    protected function getCrud(string $moduleName, string $crudName, bool $force = false)
    {
        $crud = ModelsCrud::where('module', $moduleName)->where('name', $crudName)->locked(false);
        if ($force === false) {
            $crud->generated(false);
        }
        return $crud->first();
    }

    protected function getAllCruds(string $moduleName = null)
    {
        return ModelsCrud::query()->generated(false)->locked(false)->when($moduleName, function ($query, $moduleName) {
            return $query->where('module', $moduleName);
        })->orderBy('created_at')->get();
    }

}
    