<?php

namespace Khaled\CrudSystem\Generators\Backend;

use Khaled\CrudSystem\Generators\BackendGenerator;
use Touhidurabir\StubGenerator\StubGenerator;
use Illuminate\Support\Str;
use Khaled\CrudSystem\Facades\Field;

class ServiceGenerator extends BackendGenerator
{
    
    public function checkBeforeGenerate(): bool
    {
        return $this->hasDashboardApi();
    }
    
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

    protected function getGeneratorDirectory(): string
    {
        return "{$this->modulePath}/app/Services";
    }

    protected function getLocalServiceNamespace(): string
    {
        return $this->getServiceNamespace();
    }

    protected function generateService(): void
    {
        (new StubGenerator)->from($this->getStubPath(), true)
            ->to($this->getGeneratorDirectory())
            ->withReplacers($this->getReplacers())
            ->replace(true)
            ->as($this->getServiceName())
            ->save();
    }

    protected function getReplacers(): array
    {
        return [
            'CLASS_NAMESPACE' => $this->getLocalServiceNamespace(),
            'CLASS_NAME' => $this->getServiceName(),
            'USE_CLASSES' => $this->getUseClasses(),
            'METHODS' => $this->getMethods(),
        ];
    }

    protected function getUseClasses(): string
    {
        $useClasses = [
            'use App\Helpers\CrudHelpers\Facades\CrudHelper;',
            "use {$this->getModelNamespace()}\\{$this->modelName};"
        ];
        return collect($useClasses)->implode("\n") . "\n";
    }

    protected function getMethods(): string
    {
        $methods = $this->getIndexMethod();
        if ($this->checkApiRoute('create')) $methods .= $this->getStoreMethod();
        if ($this->checkApiRoute('show') || $this->checkApiRoute('edit')) $methods .= $this->getShowMethod();
        if ($this->checkApiRoute('edit')) $methods .= $this->getUpdateMethod();
        if ($this->checkApiRoute('delete')) $methods .= $this->getDestroyMethod();
        return $methods;
    }

    protected function getIndexMethod(): string
    {
        $filters = $this->getFilters();
        $with = $this->getWith($this->getFieldsHasRelationForList(), 'before');
        return "\n\n\tpublic function tableList()\n\t{
        \$query = {$this->modelName}::query(){$with};
        return CrudHelper::tableList(\$query, [
            \App\Filters\Sorting\SortBy::class{$filters}
        ]);
    }";
    }

    protected function getShowMethod(): string
    {
        $with = $this->getWith($this->getFieldsHasRelationForShow(), 'after');
        $withTrashed = $this->hasSoftDeletes() ? 'withTrashed()->' : '';
        return "\n\n\tpublic function show(\$id)\n\t{\n\t\treturn {$this->modelName}::{$withTrashed}{$with}findOrFail(\$id);\n\t}";
    }

    protected function getStoreMethod(): string
    {
        $handleFieldsWhenCreate = $this->handleFieldsWhenCreateAndUpdate();
        $handleAddingRelations = $this->handleAddingRelations();
        return "\n\n\tpublic function create(\$data)\n\t{{$handleFieldsWhenCreate}
        \${$this->modelNameCamel} = {$this->modelName}::create(\$data);{$handleAddingRelations}
        return \${$this->modelNameCamel};
    }";
    }

    protected function getUpdateMethod(): string
    {
        $handleFieldsWhenUpdate = $this->handleFieldsWhenCreateAndUpdate('update');
        $handleAddingRelations = $this->handleAddingRelations('update');
        return "\n\n\tpublic function update(\$id, \$data)\n\t{
        \${$this->modelNameCamel} = \$this->show(\$id);{$handleFieldsWhenUpdate}
        \${$this->modelNameCamel}->update(\$data);{$handleAddingRelations}
        return \${$this->modelNameCamel};
    }";
    }

    protected function getDestroyMethod(): string
    {
        $hasPermission = $this->hasPermissions() ? ", '{$this->modelNameKebab}'" : '';
        return "\n\n\tpublic function delete(\$id)\n\t{
        return CrudHelper::deleteActions(\$id, new {$this->modelName}$hasPermission);
    }";
    }

    protected function handleFieldsWhenCreateAndUpdate($formType = 'create'): string
    {
        $fieldsHandled = $this->handleUploadFiles($formType);
        return $fieldsHandled;
    }

    protected function handleUploadFiles($formType = 'create'): string
    {
        $fileFields = $this->getFileFields();
        if (!count($fileFields)) return '';
        $fileUploads = collect($fileFields)->map(function ($field, $name) use ($formType) {
            $hasAddQuality = !Str::contains($field['type'], 'video') ? '->quality(80)' : '';
            if (Str::contains($field['type'], 'multi_')) {
                $oldValues = $formType == 'update' ? ", \${$this->modelNameCamel}->{$name}Urls" : '';
                return "\n\t\t\$data['{$name}'] = multi_uploader(request()->{$name}{$oldValues})->path((new {$this->modelName})->filePaths['multi']){$hasAddQuality}->upload();";
            }
            $addModel = $formType == 'update' ? "->model(\${$this->modelNameCamel})" : '';
            return "\n\t\t\$data['{$name}'] = uploader(){$addModel}->path((new {$this->modelName})->filePaths['single'])->fieldName('{$name}'){$hasAddQuality}->upload();";
        })->implode('');
        return $fileUploads;
    }

    protected function handleAddingRelations($formType = 'create'): string
    {
        $relations = collect($this->getModelRelations())->filter(function ($relation, $name) {
            $field = $this->getFieldByName($name);
            return $field && in_array($relation['type'], ['belongsToMany', 'morphToMany']);
        })->toArray();
        if (!count($relations)) return '';
        $addRelations = [];
        $belongsToManyMethods = $formType == 'update' ? 'sync' : 'attach';
        foreach ($relations as $name => $relation) {
            $addRelations[] = "\n\t\t\${$this->modelNameCamel}->{$name}()->{$belongsToManyMethods}(\$data['{$name}'] ?? []);";
        }
        return collect($addRelations)->implode('');
    }

    protected function getFilters(): string
    {
        $filters = [];
        if ($this->hasTableFilter()) {
            $filters[] = '\App\Filters\Date\Date::class';
            $filters[] = '\App\Filters\Date\Time::class';
            $filters[] = '\App\Filters\Search\AdvancedSearch::class';
        }
        if ($this->hasTableSearch()) $filters[] = "\App\Filters\Search\TableSearchText::class";
        if ($this->hasSoftDeletes()) $filters[] = "\App\Filters\Boolean\Trashed::class";
        if ($this->hasTableFilter()) $filters = array_merge(
            $filters,
            $this->getBooleanFilters(),
            $this->getDateFilters(),
            $this->getConstantFilters()
        );
        return count($filters) ? ',' . collect($filters)->map(fn ($filter) => "\n\t\t\t" . $filter)->implode(',') : '';
    }

    protected function getBooleanFilters(): array
    {
        $filters = [];
        $activationRouteOption = $this->getActivationRouteOption();
        $fieldNames = collect($this->getBooleanFilterFields())->pluck('name')->toArray();
        if ($activationRouteOption) array_unshift($fieldNames, $activationRouteOption['column'] ?? 'is_active');
        foreach ($fieldNames as $fieldName) {
            $filters[] = "new \App\Filters\Boolean\ToggleBoolean('{$fieldName}')";
        }
        return $filters;
    }

    protected function getDateFilters(): array
    {
        $filters = [];
        $fieldNames = collect($this->getDateFilterFields())->pluck('name')->toArray();
        foreach ($fieldNames as $fieldName) {
            $filters[] = "new \App\Filters\Date\Date('{$fieldName}')";
        }
        return $filters;
    }

    protected function getConstantFilters(): array
    {
        $filters = [];
        foreach ($this->getConstantFilterFields() as $name => $field) {
            $databaseType = Field::isSingleConstant($field) ? 'single' : 'multi';
            if ($databaseType === 'single') {
                $filters[] = "new \App\Filters\General\EqualFilter('{$name}')";
            } else if ($databaseType === 'multi') { // && $filterType === 'multi' -- الفلتر فى الفرونت ملتى يبعت قيمة او اكتر عادى
                $filters[] = "new \App\Filters\General\ArrayCheckMultiFilter('{$name}')";
            }
        }
        foreach ($this->getModelLookupFilterFields() as $name => $field) {
            if (Field::hasFilterRelation($field)) {
                $relationName = Field::getFilterRelation($field);
                $relationColumnName = Field::getFilterRelationColumnName($field);
                $filters[] = "new \App\Filters\General\RelationFilter('{$name}', '{$relationName}', '{$relationColumnName}')";
            } else {
                $filters[] = "new \App\Filters\General\EqualFilter('{$name}')";
            }
        }
        return $filters;
    }

    protected function getFieldsHasRelationForList(): array
    {
        return collect($this->getFieldsVisibleInList())->filter(fn ($field) => Field::hasRelation($field))->toArray();
    }

    protected function getFieldsHasRelationForShow(): array
    {
        return collect($this->getFieldsVisibleInFormAndView())->filter(fn ($field) => Field::hasRelation($field))->toArray();
    }

    protected function getWith(array $fields = [], $arrow = 'before'): string
    {
        $with = [];
        foreach ($fields as $field) {
            $with[] = Field::getRelationName($field);
        }
        $with = count($with) ? 'with(' . collect($with)->map(fn($relation) => "'{$relation}'")->implode(', ') . ')' : '';
        return $with ? ($arrow == 'before' ? "->{$with}" : "{$with}->") : '';
    }

}
