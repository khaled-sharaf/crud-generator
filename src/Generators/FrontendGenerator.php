<?php

namespace W88\CrudSystem\Generators;

use W88\CrudSystem\Facades\Crud;
use Illuminate\Support\Facades\File;

abstract class FrontendGenerator extends Generator
{
    
    protected function ensureVueStubExists($type = 'vue'): void
    {
        $stubPath = $type === 'vue' ? $this->getVueStubPath() : $this->getJsStubPath();
        if (!File::exists($stubPath)) {
            throw new \Exception("Stub file not found at path: {$stubPath}");
        }
    }
    
    protected function getFrontendModulePath(): string
    {
        $frontendPath = Crud::config('generator.frontend_path');
        return base_path("/{$frontendPath}/src/modules/{$this->frontendModuleName}");
    }

    protected function getFrontendCrudPath(): string
    {
        return $this->getFrontendModulePath() . "/cruds/{$this->modelNameCamel}";
    }

    protected function hasShowPopup(): bool
    {
        return $this->config['options']['showPopup'] ?? false;
    }

    protected function hasFormPopup(): bool
    {
        return $this->config['options']['formPopup'] ?? false;
    }

    protected function getApiRouteName(): string
    {
        return "{$this->moduleNameKebab}/{$this->modelNameKebabPlural}";
    }

    protected function getListFileName(): string
    {
        return "{$this->modelName}List";
    }

    protected function getCreateFileName(): string
    {
        return "Create{$this->modelName}";
    }

    protected function getEditFileName(): string
    {
        return "Edit{$this->modelName}";
    }

    protected function getShowFileName(): string
    {
        return "View{$this->modelName}";
    }
    
    protected function getFormFileName(): string
    {
        return "{$this->modelName}Form";
    }

    protected function getListRouteName(): string
    {
        return "{$this->modelNameKebab}-list";
    }

    protected function getCreateRouteName(): string
    {
        return "{$this->modelNameKebab}-create";
    }

    protected function getEditRouteName(): string
    {
        return "{$this->modelNameKebab}-edit";
    }

    protected function getShowRouteName(): string
    {
        return "{$this->modelNameKebab}-view";
    }
    
}