<?php

namespace W88\CrudSystem\Generators;

use W88\CrudSystem\Facades\Crud;
use Illuminate\Support\Facades\File;
use W88\CrudSystem\Facades\Field;
use Illuminate\Support\Str;
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

    protected function hasMultiSelection(): bool
    {
        return $this->config['options']['tableSettings']['multiSelection'] ?? true;
    }

    protected function getApiRouteName(): string
    {
        return "{$this->moduleNameKebab}/{$this->modelNameKebabPlural}";
    }

    protected function getTableId(): string
    {
        return $this->modelNameCamelPlural;
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

    protected function getLangPath(string $key = null): string
    {
        return "{$this->frontendModuleName}.{$this->modelNameSnake}_crud" . ($key ? ".{$key}" : '');
    }

    protected function getLookupFile(string $name = null): string
    {
        return $this->modelName . Str::studly($name);
    }

    protected function getLookupApiRouteName(string $name = null): string
    {
        return $this->modelNameKebab . '-' . Str::kebab($name);
    }

    protected function getLookupName(string $name = null): string
    {
        return "{$this->getLookupFile($name)}Lookup";
    }

    protected function getFieldsVisibleInList(): array
    {
        return collect($this->getFields())->filter(fn ($field) => !Field::isHiddenList($field))->toArray();
    }

    protected function getFieldsVisibleInView(): array
    {
        return collect($this->getFields())->filter(fn ($field) => !Field::isHiddenShow($field))->toArray();
    }

    protected function getFieldsHasLookupFrontend(): array
    {
        return collect($this->getNotHiddenFields())->filter(fn ($field) => Field::hasLookupFrontend($field))->toArray();
    }

    protected function getFieldsHasBackendLookupOnly(): array
    {
        return collect($this->getConstantFilterFields())->filter(fn ($field) => !Field::hasLookupFrontend($field) && Field::hasLookup($field))->toArray();
    }

    protected function getFieldsHasModelLookup(): array
    {
        return collect($this->getNotHiddenFields())->filter(fn ($field) => Field::hasLookupModel($field))->toArray();
    }

    protected function getTitleTrue(array $field): string
    {
        return $field['name'] == 'is_active' ? 'active' : ($field['type'] == 'checkbox' ? 'checked' : 'enabled');
    }
    
    protected function getTitleFalse(array $field): string
    {
        return $field['name'] == 'is_active' ? 'deactive' : ($field['type'] == 'checkbox' ? 'unchecked' : 'disabled');
    }
    
}