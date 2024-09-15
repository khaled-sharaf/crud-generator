<?php

namespace W88\CrudSystem\Traits;
use Illuminate\Support\Str;

trait GeneratorHelpers
{

    /* ======================== Getters ======================== */
    protected function modelNamespace(): string
    {
        return $this->moduleNamespace . '\app\Models';
    }

    protected function getControllerName(): string
    {
        return $this->modelName . 'Controller';
    }

    protected function getControllerDirectory(): string
    {
        return "{$this->modulePath}/app/Http/Controllers/{$this->versionNamespace}";
    }

    protected function getControllerNamespace(): string
    {
        return "{$this->moduleNamespace}\app\Http\Controllers\\{$this->versionNamespace}";
    }

    protected function getServiceName(): string
    {
        return $this->modelName . 'Service';
    }

    protected function getServiceNameCamel(): string
    {
        return Str::camel($this->getServiceName());
    }

    protected function getServiceDirectory(): string
    {
        return "{$this->modulePath}/app/Services/{$this->versionNamespace}";
    }

    protected function getServiceNamespace(): string
    {
        return "{$this->moduleNamespace}\app\Services\\{$this->versionNamespace}";
    }

    protected function getRequestNamespace(): string
    {
        return "{$this->moduleNamespace}\app\Http\Requests\\{$this->versionNamespace}";
    }

    protected function getRequestName(): string
    {
        return $this->modelName . 'Request';
    }

    protected function getResourceName(): string
    {
        return $this->modelName . 'Resource';
    }

    protected function getResourceNamespace(): string
    {
        return "{$this->moduleNamespace}\app\Http\Resources\\{$this->versionNamespace}";
    }
    
    /* ======================== Checks ======================== */
    protected function hasCreateRoute(): bool
    {
        return isset($this->config['dashboard']['create']) && $this->config['dashboard']['create'] === true;
    }

    protected function hasProfileRoute(): bool
    {
        return isset($this->config['dashboard']['profile']) && $this->config['dashboard']['profile'] === true;
    }

    protected function hasUpdateRoute(): bool
    {
        return isset($this->config['dashboard']['update']) && $this->config['dashboard']['update'] === true;
    }

    protected function hasDeleteRoute(): bool
    {
        return isset($this->config['dashboard']['delete']) && $this->config['dashboard']['delete'] === true;
    }

    protected function hasActivationRoute(): bool
    {
        return isset($this->config['dashboard']['activation']) && $this->config['dashboard']['activation'] === true;
    }

    protected function hasPermissions(): bool
    {
        return isset($this->config['permissions']) && $this->config['permissions'] === true;
    }

    protected function hasSoftDeletes(): bool
    {
        return isset($this->config['soft_deletes']) && $this->config['soft_deletes'] === true;
    }

    protected function hasTimestamps(): bool
    {
        return isset($this->config['timestamps']) && $this->config['timestamps'] === true;
    }

    protected function hasSeeder(): bool
    {
        return isset($this->config['seeder']);
    }

    /* ======================== Fields ======================== */
    protected function getFields(): array
    {
        return $this->config['fields'] ?? [];
    }
}