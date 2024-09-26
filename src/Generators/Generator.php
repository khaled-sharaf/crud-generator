<?php

namespace W88\CrudSystem\Generators;

use W88\CrudSystem\Contracts\GeneratorInterface;
use Illuminate\Support\Str;
use W88\CrudSystem\Facades\Crud;
use W88\CrudSystem\Traits\GeneralHelpersTrait;

abstract class Generator implements GeneratorInterface
{
    use GeneralHelpersTrait;

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
        $this->frontendModuleName = $this->config['frontendModule'];

        $this->clientDirectory = Str::studly(Crud::config('generator.client_directory'));
        
        $this->modelName = Str::studly($this->config['name']);
        $this->modelNamespace = $this->moduleNamespace . '\\app\\Models';
        $this->modelNameCamel = Str::camel($this->modelName);
        $this->modelNameSnake = strtolower(Str::snake($this->modelName));
        $this->modelNameKebab = strtolower(Str::kebab($this->modelName));
        $this->modelNameCamelPlural = Str::plural($this->modelNameCamel);
        $this->modelNameSnakePlural = Str::plural($this->modelNameSnake);
        $this->modelNameKebabPlural = Str::plural($this->modelNameKebab);
    }
    
}