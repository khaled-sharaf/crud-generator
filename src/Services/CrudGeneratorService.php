<?php

namespace W88\CrudSystem\Services;

use Illuminate\Support\Facades\File;
use W88\CrudSystem\Facades\Crud;
use Illuminate\Support\Str;

class CrudGeneratorService
{

    public function generate($moduleName = null, $crudName = null)
    {
        if ($moduleName && $crudName) {
            $this->singleGenerator($moduleName, $crudName);
        } else {
            $this->multipleGenerator();
        }
    }

    protected function singleGenerator($moduleName, $crudName)
    {
        $version = strtolower(config('app.api_version', 'v1'));
        $config = $this->loadCrudClientConfig($moduleName, $crudName);
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

    protected function multipleGenerator()
    {
        $modules = File::directories(base_path('Modules'));
        foreach ($modules as $module) {
            $moduleName = basename($module);
            $crudPath = $module . '/config/cruds';
            if (!File::exists($crudPath)) continue;
            $cruds = File::files($crudPath);
            foreach ($cruds as $crud) {
                $crudName = pathinfo(basename($crud), PATHINFO_FILENAME);
                $this->singleGenerator($moduleName, $crudName);
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

    protected function loadCrudClientConfig($moduleName, $crudName)
    {
        $configPath = base_path("Modules/{$moduleName}/config/cruds/" . $crudName . '.php');
        if (!File::exists($configPath)) {
            throw new \Exception('Config not found for ' . $moduleName . ' ' . $crudName);
        }
        return File::getRequire($configPath);
    }

}
    