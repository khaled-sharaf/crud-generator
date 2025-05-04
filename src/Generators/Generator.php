<?php

namespace Khaled\CrudSystem\Generators;

use Khaled\CrudSystem\Contracts\GeneratorInterface;
use Illuminate\Support\Str;
use Khaled\CrudSystem\Facades\Crud;
use Khaled\CrudSystem\Facades\Field;
use Illuminate\Support\Facades\File;

abstract class Generator implements GeneratorInterface
{

    protected array $configData;
    protected array $config;

    protected string $version;
    protected string $versionNamespace;

    protected string $moduleName;
    protected string $moduleNamespace;
    protected string $moduleNameSnake;
    protected string $moduleNameKebab;
    protected string $modulePath;
    protected string $frontendModuleName;

    protected string $clientDirectory;

    protected string $modelName;
    protected string $modelNamespace;
    protected string $modelNameCamel;
    protected string $modelNameSnake;
    protected string $modelNameKebab;
    protected string $modelNameCamelPlural;
    protected string $modelNameSnakePlural;
    protected string $modelNameKebabPlural;

    public function __construct(array $configData)
    {
        $this->configData = $configData;
        $this->config = $configData['config'];
        $this->version = $configData['version'];
        $this->versionNamespace = Str::studly($configData['version']);
        
        $this->moduleName = $configData['moduleName'];
        $this->moduleNamespace = "Modules\\{$this->moduleName}";
        $this->moduleNameSnake = strtolower(Str::snake($this->moduleName));
        $this->moduleNameKebab = strtolower(Str::kebab($this->moduleName));
        $this->modulePath = module_path($this->moduleName);
        $this->frontendModuleName = $configData['frontendModule'];
        
        $this->clientDirectory = Str::studly(Crud::config('generator.client_directory'));
        
        $this->modelName = $configData['modelName'];
        $this->modelNamespace = $this->moduleNamespace . '\\app\\Models';
        $this->modelNameCamel = Str::camel($this->modelName);
        $this->modelNameSnake = strtolower(Str::snake($this->modelName));
        $this->modelNameKebab = strtolower(Str::kebab($this->modelName));
        $this->modelNameCamelPlural = Str::plural($this->modelNameCamel);
        $this->modelNameSnakePlural = Str::plural($this->modelNameSnake);
        $this->modelNameKebabPlural = Str::plural($this->modelNameKebab);
    }
    
    /* ======================== Generals ======================== */
    
    protected function ensureStubExists(): void
    {
        $stubPath = $this->getStubPath();
        if (!File::exists($stubPath)) {
            throw new \Exception("Stub file not found at path: {$stubPath}");
        }
    }

    protected function ensureDirectoryExists(): void
    {
        $directory = $this->getGeneratorDirectory();
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    /* ======================== Checks ======================== */

    protected function hasDashboardApi(): bool
    {
        $dashboardApi = $this->config['dashboardApi'] ?? false;
        if (is_array($dashboardApi)) $dashboardApi = count($dashboardApi) && !collect($dashboardApi)->every(fn ($route) => $route === false);
        return boolval($dashboardApi);
    }

    protected function hasClientApi(): bool
    {
        $clientApi = $this->config['clientApi'] ?? false;
        if (is_array($clientApi)) $clientApi = count($clientApi) && !collect($clientApi)->every(fn ($route) => $route === false);
        return boolval($clientApi);
    }
    
    protected function checkApiRoute($route, $type = 'dashboardApi'): bool|array
    {
        $checkClientApi = $type === 'clientApi' ? $this->allClientApiIsAllowed() : false;
        return $this->config[$type ?? 'dashboardApi'][$route] ?? $checkClientApi;
    }

    protected function allClientApiIsAllowed(): bool
    {
        $clientApi = $this->config['clientApi'] ?? false;
        return $clientApi === true || (is_array($clientApi) && count($clientApi) === 5 && collect($clientApi)->every(fn ($route) => $route === true));
    }

    protected function hasPermissions(): bool
    {
        return $this->config['options']['permissions'] ?? false;
    }

    protected function hasSoftDeletes(): bool
    {
        return $this->config['options']['softDeletes'] ?? false;
    }

    protected function hasTableSearch(): bool
    {
        return $this->config['options']['tableSettings']['tableSearch'] ?? true;
    }

    protected function hasTableFilter(): bool
    {
        return $this->config['options']['tableSettings']['tableFilter'] ?? true;
    }

    protected function hasTableExport(): bool
    {
        return $this->config['options']['tableSettings']['tableExport'] ?? true;
    }

    /* ======================== Getters ======================== */
    protected function getActivationRouteOption()
    {
        return $this->config['dashboardApi']['activation'] ?? false;
    }
    
    protected function getFields(): array
    {
        return collect(array_merge($this->config['fields'] ?? [], $this->appendActivationField()))
        ->mapWithKeys(fn ($field, $name) => [strtolower(Str::snake($name)) => $field])
        ->map(function ($field, $name) {
            $field['name'] = $name;
            return $field;
        })->toArray();
    }

    private function appendActivationField(): array
    {
        $fields = [];
        $activationRouteOption = $this->getActivationRouteOption();
        $activationColumn = $activationRouteOption['column'] ?? 'is_active';
        $activationDefault = $activationRouteOption['default'] ?? true;
        if ($activationRouteOption) {
            $fields[$activationColumn] = [
                'type' => 'boolean',
                'label' => 'Activation',
                'default' => $activationDefault,
                'validation' => 'nullable|boolean',
                'frontend' => [
                    'visibleList' => true,
                    'sortable' => true,
                ],
            ];
        }
        return $fields;
    }

    protected function getFieldByName(string $name): array|null
    {
        return collect($this->getFields())->filter(fn ($field) => $field['name'] === $name)->first();
    }

    protected function getFileFields(): array
    {
        return collect($this->getFields())->filter(fn ($field) => Field::hasFile($field))->toArray();
    }

    protected function getBooleanFields(): array
    {
        return collect($this->getFields())->filter(fn ($field) => Field::isBoolean($field))->toArray();
    }

    protected function getBooleanFieldsVisibleInList(): array
    {
        return collect($this->getFieldsVisibleInList())->filter(fn ($field) => Field::isBoolean($field))->toArray();
    }

    protected function getConstantFields(): array
    {
        return collect($this->getFields())->filter(fn ($field) => Field::hasConstant($field))->toArray();
    }

    protected function getConstantFieldsVisibleInList(): array
    {
        return collect($this->getFieldsVisibleInList())->filter(fn ($field) => Field::hasConstant($field))->toArray();
    }
    
    protected function getNotHiddenFields(): array
    {
        return collect($this->getFields())->filter(fn ($field) => !Field::isHidden($field))->toArray();
    }

    protected function getBooleanRouteFields(): array
    {
        return collect($this->getFields())->filter(fn ($field) => Field::hasBooleanRouteFilter($field))->toArray();
    }

    protected function getBooleanFilterFields(): array
    {
        return collect($this->getFields())->filter(fn ($field) => Field::hasBooleanFilter($field))->toArray();
    }

    protected function getDateFilterFields(): array
    {
        return collect($this->getFields())->filter(fn ($field) => Field::hasDateFilter($field))->toArray();
    }

    protected function getConstantFilterFields(): array
    {
        return collect($this->getFields())->filter(fn ($field) => Field::hasConstantFilter($field))->toArray();
    }

    protected function getModelLookupFilterFields(): array
    {
        return collect($this->getFields())->filter(fn ($field) => Field::isFilterable($field) && Field::hasLookupModel($field))->toArray();
    }

    protected function getFieldsVisibleInList(): array
    {
        return collect($this->getFields())->filter(fn ($field) => !Field::isHiddenList($field))->toArray();
    }

    protected function getFieldsVisibleInView(): array
    {
        return collect($this->getFields())->filter(fn ($field) => !Field::isHiddenShow($field))->toArray();
    }

    protected function getFieldsVisibleInForm(): array
    {
        return collect($this->getFields())->filter(fn ($field) => !Field::isHiddenEdit($field) || !Field::isHiddenCreate($field))->toArray();
    }

    protected function getFieldsVisibleInFormAndView(): array
    {
        return collect($this->getFields())->filter(fn ($field) => !Field::isHiddenEdit($field) || !Field::isHiddenCreate($field) || !Field::isHiddenShow($field))->toArray();
    }

    protected function getTranslatableFields(): array
    {
        return collect($this->getFields())->filter(fn ($field) => Field::isTranslatable($field))->toArray();
    }

    protected function getBackendTranslatableFields(): array
    {
        return collect($this->getFields())->filter(fn ($field) => Field::isBackendTranslatable($field))->toArray();
    }

    /* ======================== Helpers ======================== */

    protected function removeLeadingTab($text, $countTabs = 1) {
        // Split the text into lines
        $lines = explode("\n", $text);
        $pattern = '/^' . str_repeat('\t', $countTabs) . '/';
        // Remove leading tab from each line
        $cleanedLines = array_map(function($line) use ($pattern) {
            return preg_replace($pattern, '', $line);
        }, $lines);
        
        // Join the lines back together
        return implode("\n", $cleanedLines);
    }
    
}