<?php

namespace W88\CrudSystem\Generators\Backend;

use W88\CrudSystem\Generators\Generator;
use Illuminate\Support\Facades\File;
use Touhidurabir\StubGenerator\Facades\StubGenerator;
use Illuminate\Support\Str;

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
        return "{$this->modulePath}/app/Services";
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
        return CrudHelper::tableList(new {$this->modelName}, [
            \App\Filters\Sorting\SortBy::class{$filters}
        ]);
    }";
    }

    protected function getShowMethod(): string
    {
    return "\n\n\tpublic function show(\$id)\n\t{\n\t\treturn {$this->modelName}::findOrFail(\$id);\n\t}";
    }

    protected function getStoreMethod(): string
    {
        $handleFieldsWhenCreate = $this->handleFieldsWhenCreateAndUpdate();
        return "\n\n\tpublic function create(\$data)
    {{$handleFieldsWhenCreate}
        \${$this->modelNameCamel} = {$this->modelName}::create(\$data);
        return \${$this->modelNameCamel};
    }";
    }

    protected function getUpdateMethod(): string
    {
        $handleFieldsWhenUpdate = $this->handleFieldsWhenCreateAndUpdate('update');
        return "\n\n\tpublic function update(\${$this->modelNameCamel}, \$data)
    {{$handleFieldsWhenUpdate}
        \${$this->modelNameCamel}->update(\$data);
        return \${$this->modelNameCamel};
    }";
    }

    protected function getFilters(): string
    {
        $activationRouteOption = $this->getActivationRouteOption();
        $column = $activationRouteOption['column'] ?? 'is_active';
        $filters = [];
        if ($this->hasTableFilter()) {
            $filters[] = '\App\Filters\Date\Date::class';
            $filters[] = '\App\Filters\Date\Time::class';
            $filters[] = '\App\Filters\Search\AdvancedSearch::class';
        }
        if ($this->hasTableSearch()) $filters[] = "\App\Filters\Search\TableSearchText::class";
        if ($this->hasSoftDeletes()) $filters[] = "\App\Filters\Boolean\Trashed::class";
        if ($this->hasTableFilter() && $activationRouteOption) $filters[] = "new \App\Filters\Boolean\ToggleBoolean('{$column}')";
        return count($filters) ? ',' . collect($filters)->map(fn ($filter) => "\n\t\t\t" . $filter)->implode(',') : '';
    }

    protected function handleFieldsWhenCreateAndUpdate($formType = 'create'): string
    {
        $fileFields = $this->getFileFields();
        if (!count($fileFields)) return '';
        $fileUploads = collect($fileFields)->map(function ($field, $name) use ($formType) {
            $hasAddQuality = !Str::contains($field['type'], 'video') ? '->quality(80)' : '';
            if (Str::contains($field['type'], 'multi_')) {
                $oldValues = $formType == 'update' ? ", \${$this->modelNameCamel}->{$name}" : '';
                return "\n\t\t\$data['{$name}'] = multi_uploader(request()->{$name}{$oldValues})->path((new {$this->modelName})->filePaths['multi']){$hasAddQuality}->upload();";
            }
            $addModel = $formType == 'update' ? "->model(\${$this->modelNameCamel})" : '';
            return "\n\t\t\$data['{$name}'] = uploader(){$addModel}->path((new {$this->modelName})->filePaths['single'])->fieldName('{$name}'){$hasAddQuality}->upload();";
        })->implode('');
        return $fileUploads;
    }

}
