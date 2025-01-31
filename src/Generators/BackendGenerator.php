<?php

namespace Khaled\CrudSystem\Generators;

use Illuminate\Support\Str;
use Khaled\CrudSystem\Facades\Field;

abstract class BackendGenerator extends Generator
{
    
    /* ======================== Checks ======================== */
    protected function hasAddLogs(): bool
    {
        return $this->config['options']['addLogs'] ?? false;
    }

    protected function hasSeeder()
    {
        return $this->config['options']['seeder'] ?? false;
    }

    /* ======================== Getters ======================== */
    protected function getModelNamespace(): string
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
        return "{$this->moduleNamespace}\app\Services";
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
        return "{$this->moduleNamespace}\app\Resources\\{$this->versionNamespace}";
    }

    protected function getConstantNamespace(): string
    {
        return "{$this->moduleNamespace}\app\Constants\\{$this->modelName}";
    }
    
    protected function getSeederName(): string
    {
        return $this->modelName . 'Seeder';
    }

    protected function getLookupRouteOption()
    {
        return $this->config['dashboardApi']['lookup'] ?? false;
    }

    protected function getLookupFields(): array
    {
        return collect($this->getFields())->filter(fn ($field) => Field::hasLookup($field))->toArray();
    }

    protected function getConstantName(array $field): string
    {
        return $this->modelName . Str::studly($field['name']);
    }

    protected function getCastFields(): array
    {
        return collect($this->getFields())->map(function ($field) {
            $field['cast'] = array_key_exists($field['type'], Field::jsonFields()) ? 'array' : (in_array($field['type'], ['boolean', 'checkbox']) ? 'boolean' : null);
            return $field;
        })->filter(fn ($field) => $field['cast'] !== null)->toArray();
    }

    protected function getDateFields(): array
    {
        return collect($this->getFields())->filter(fn ($field) => Field::isDate($field))->toArray();
    }

    protected function getPermissionsTranslated(): array
    {
        $modelTitle = Str::title(Str::replace('-', ' ', $this->modelNameKebab));
        $permissions = [];
        if ($this->hasDashboardApi()) $permissions["view-list-{$this->modelNameKebab}"] = "View {$modelTitle} List";
        if ($this->hasTableExport()) $permissions["export-list-{$this->modelNameKebab}"] = "Export {$modelTitle} List";
        if ($this->checkApiRoute('show')) $permissions["view-{$this->modelNameKebab}"] = "View {$modelTitle}";
        if ($this->checkApiRoute('create')) $permissions["create-{$this->modelNameKebab}"] = "Create {$modelTitle}";
        if ($this->checkApiRoute('edit')) $permissions["edit-{$this->modelNameKebab}"] = "Edit {$modelTitle}";
        if ($this->checkApiRoute('delete')) $permissions["delete-{$this->modelNameKebab}"] = "Delete {$modelTitle}";
        if ($this->hasSoftDeletes()) {
            $permissions["force-delete-{$this->modelNameKebab}"] = "Delete Forever {$modelTitle}";
            $permissions["restore-{$this->modelNameKebab}"] = "Restore {$modelTitle}";
            $permissions["view-trashed-{$this->modelNameKebab}-list"] = "View Trashed {$modelTitle} List";
        }
        if ($this->getActivationRouteOption()) $permissions["activation-{$this->modelNameKebab}"] = "Activation {$modelTitle}";
        foreach ($this->getBooleanRouteFields() as $field) {
            $permissionName = strtolower(Str::kebab($field['route']));
            $permissionTitle = Str::title(Str::replace('-', ' ', $permissionName));
            $permissions["{$permissionName}-{$this->modelNameKebab}"] = "{$permissionTitle} {$modelTitle}";
        }
        return $permissions;
    }

    protected function getModelRelations(): array
    {
        $relations = array_merge($this->appendRelationFields(), $this->config['relations'] ?? []);
        return collect($relations)->filter(fn ($relation) => isset($relation['type']) && in_array($relation['type'], $this->getAllowedRelations()))->toArray();
    }

    private function appendRelationFields(): array
    {
        $relations = [];
        foreach ($this->getFields() as $name => $field) {
            if (Field::hasRelation($field) && $relationModel = Field::getRelationModel($field)) {
                $isEndById = Str::endsWith($name, '_id');
                $relationName = Field::getRelationName($field);
                $relations[$relationName] = [
                    'type' => Field::getRelationType($field),
                    'model' => $relationModel,
                ];
                if (!$isEndById) $relations[$relationName]['foreignKey'] = $name;
            }
        }
        return $relations;
    }
    
    private function getAllowedRelations(): array
    {
        return [
            'belongsTo',
            'hasOne',
            'hasMany',
            'belongsToMany',
            'morphTo',
            'morphOne',
            'morphMany',
            'morphToMany',
            'morphedByMany',
        ];
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

    /* ======================== Helpers ======================== */
    protected function makePolymorphic($name) {
        if (preg_match('/([aeiou])([bcdfghjklmnpqrstvwxyz])$/i', $name)) $name = $name . substr($name, -1);
        return $name . 'able';
    }
    
}