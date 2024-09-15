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

    protected function getServiceNamespace(): string
    {
        return "{$this->moduleNamespace}\app\Services\\{$this->versionNamespace}";
    }

    protected function getRequestName(): string
    {
        return $this->modelName . 'Request';
    }

    protected function getRequestNamespace(): string
    {
        return "{$this->moduleNamespace}\app\Http\Requests\\{$this->versionNamespace}";
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

    protected function hasTableExport(): bool
    {
        return isset($this->config['dashboard']['tableExport']) && $this->config['dashboard']['tableExport'] === true;
    }

    protected function hasPermissions(): bool
    {
        return isset($this->config['permissions']) && $this->config['permissions'] === true;
    }

    protected function hasSoftDeletes(): bool
    {
        return isset($this->config['softDeletes']) && $this->config['softDeletes'] === true;
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

    /* ======================== Permissions ======================== */
    protected function getPermissionsTranslated(): array
    {
        $modelTitle = Str::title($this->modelNameKebab);
        $permissions = [
            "view-list-{$this->modelNameKebab}" => "View {$modelTitle} List"
        ];
        if ($this->hasTableExport()) $permissions["export-list-{$this->modelNameSnake}"] = "Export {$modelTitle} List";
        if ($this->hasProfileRoute()) $permissions["view-profile-{$this->modelNameSnake}"] = "View {$modelTitle} Profile";
        if ($this->hasCreateRoute()) $permissions["create-{$this->modelNameSnake}"] = "Create {$modelTitle}";
        if ($this->hasUpdateRoute()) $permissions["edit-{$this->modelNameSnake}"] = "Edit {$modelTitle}";
        if ($this->hasDeleteRoute()) $permissions["delete-{$this->modelNameSnake}"] = "Delete {$modelTitle}";
        if ($this->hasSoftDeletes()) {
            $permissions["force-delete-{$this->modelNameSnake}"] = "Delete Forever {$modelTitle}";
            $permissions["restore-{$this->modelNameSnake}"] = "Restore {$modelTitle}";
            $permissions["view-trashed-{$this->modelNameSnake}-list"] = "View Trashed {$modelTitle} List";
        }
        if ($this->hasActivationRoute()) $permissions["activation-{$this->modelNameSnake}"] = "Activation {$modelTitle}";
        return $permissions;
    }

    /* ======================== Helpers ======================== */
    protected function isPhpCode($string) {
        $patterns = [
            '/\$[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*/', // PHP variables like $this
            '/::/', // Static method or constant calls like Rule::unique
            '/->/', // Object method or property access like $this->post
            '/\(.*\)/', // Function calls with parentheses
            '/\s*new\s+/', // Object instantiation
        ];
        // Check if any of the patterns match the string
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $string)) {
                return true; // Contains PHP code
            }
        }
        return false; // Doesn't contain PHP code
    }
}