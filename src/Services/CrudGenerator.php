<?php

namespace W88\CrudSystem\Services;

use Illuminate\Support\Facades\File;
use W88\CrudSystem\Factories\CrudGeneratorFactory;

class CrudGenerator
{

    // private $backend_generator_types = ['migration', 'model', 'route', 'controller', 'service', 'request', 'resource', 'seeder', 'lang', 'permission'];
    private $backend_generator_types = ['migration', 'model', 'controller', 'service', 'request', 'resource', 'seeder', 'permission'];
    private $frontend_generator_types = ['index', 'create', 'edit', 'show'];
    
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
        $config = $this->loadConfig($moduleName, $crudName);
        if (!$config) {
            throw new \Exception('Config not found for ' . $moduleName . ' ' . $crudName);
        }
        foreach ($this->getGeneratorsTypes() as $generator_type) {
            $generators_action = $this->getGeneratorsAction($generator_type);
            $configData = [
                'moduleName' => $moduleName,
                'config' => $config,
                'version' => $version,
            ];
            $generator = CrudGeneratorFactory::create($generators_action, $generator_type, $configData);
            $generator->generate();
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

    protected function getGeneratorsAction($generator_type)
    {
        return in_array($generator_type, $this->backend_generator_types) ? 'backend' : 'frontend';
    }

    protected function getGeneratorsTypes()
    {
        // return array_merge($this->backend_generator_types, $this->frontend_generator_types);
        return $this->backend_generator_types;
    }

    protected function loadConfig($moduleName, $crudName)
    {
        $configPath = base_path("Modules/{$moduleName}/config/cruds/" . $crudName . '.php');
        if (!File::exists($configPath)) return null;
        return File::getRequire($configPath);
    }

}
    