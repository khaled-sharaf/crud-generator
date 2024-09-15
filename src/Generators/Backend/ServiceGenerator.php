<?php

namespace W88\CrudSystem\Generators\Backend;

use W88\CrudSystem\Generators\Generator;
use Illuminate\Support\Facades\File;
use Touhidurabir\StubGenerator\Facades\StubGenerator;

class ServiceGenerator extends Generator
{

    public function generate(): void
    {
        $this->ensureStubExists();
        $this->ensureDirectoryExists();
        $this->generateService();
    }

    protected function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/backend/service.stub';
    }

    protected function getServiceDirectory(): string
    {
        return "{$this->modulePath}/app/Services/{$this->versionNamespace}";
    }

    protected function ensureStubExists(): void
    {
        $stubPath = $this->getStubPath();
        if (!File::exists($stubPath)) {
            throw new \Exception("Stub file not found at path: {$stubPath}");
        }
    }

    protected function ensureDirectoryExists(): void
    {
        $directory = $this->getServiceDirectory();
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    protected function generateService(): void
    {
        StubGenerator::from($this->getStubPath(), true)
            ->to($this->getServiceDirectory())
            ->withReplacers($this->getReplacers())
            ->replace(true)
            ->as($this->getServiceName())
            ->save();
    }

    protected function getReplacers(): array
    {
        return [
            'CLASS_NAMESPACE' => $this->getServiceNamespace(),
            'CLASS_NAME' => $this->getServiceName(),
            'USE_CLASSES' => $this->getUseClasses(),
            'METHODS' => $this->getMethods(),
        ];
    }

    protected function getUseClasses(): string
    {
        $useClasses = [
            'use App\Helpers\CrudHelpers\Facades\CrudHelper;',
            'use ' . $this->getModelNamespace() . '\\' . $this->modelName . ';'
        ];
        return collect($useClasses)->implode("\n") . "\n";
    }

    protected function getMethods(): string
    {
        $methods = $this->getIndexMethod();
        if ($this->hasProfileRoute()) $methods .= $this->getShowMethod();
        if ($this->hasCreateRoute()) $methods .= $this->getStoreMethod();
        if ($this->hasUpdateRoute()) $methods .= $this->getUpdateMethod();
        return $methods;
    }

    protected function getIndexMethod(): string
    {
        $filters = $this->getFilters();
        return "public function tableList()
    {
        return CrudHelper::tableList(new {$this->modelName}, [{$filters}
            \App\Filters\Date\Date::class,
            \App\Filters\Date\Time::class,
            \App\Filters\Search\AdvancedSearch::class,
            \App\Filters\Search\TableSearchText::class,
            \App\Filters\Sorting\SortBy::class,
        ]);
    }";
    }

    protected function getShowMethod(): string
    {
    return "\n\n\tpublic function show(\$id)
    {
        return {$this->modelName}::findOrFail(\$id);
    }";
    }

    protected function getStoreMethod(): string
    {
        $handleFieldsWhenCreate = $this->handleFieldsWhenCreate();
        return "\n\n\tpublic function create(\$data)
    {
        {$handleFieldsWhenCreate}\${$this->modelNameCamel} = {$this->modelName}::create(\$data);
        return \${$this->modelNameCamel};
    }";
    }

    protected function getUpdateMethod(): string
    {
        $handleFieldsWhenUpdate = $this->handleFieldsWhenUpdate();
        return "\n\n\tpublic function update(\${$this->modelNameCamel}, \$data)
    {
        {$handleFieldsWhenUpdate}\${$this->modelNameCamel}->update(\$data);
        return \${$this->modelNameCamel};
    }";
    }

    protected function getFilters(): string
    {
        $filters = [];
        if ($this->hasSoftDeletes()) $filters[] = "\App\Filters\Boolean\Trashed::class,";
        if ($this->hasActivationRoute()) $filters[] = "new \App\Filters\Boolean\ToggleBoolean('is_active'),";
        return collect($filters)->map(fn ($filter) => "\n\t\t\t" . $filter)->implode('');
    }

    protected function handleFieldsWhenCreate(): string
    {
        return '';
        return "\n\t\t";
    }

    protected function handleFieldsWhenUpdate(): string
    {
        return '';
        return "\n\t\t";
    }
}
