<?php

namespace Khaled\CrudSystem\Generators\Backend;

use Khaled\CrudSystem\Generators\BackendGenerator;
use Touhidurabir\StubGenerator\StubGenerator;
use Illuminate\Support\Str;
use Khaled\CrudSystem\Facades\Field;

class ModelGenerator extends BackendGenerator
{
    
    protected MigrationGenerator $migrationGenerator;
    protected $belongsToManyMigrationName;
    protected $morphToManyMigrationName;

    public function checkBeforeGenerate(): bool
    {
        return true;
    }
    
    public function generate(): void
    {
        $this->ensureStubExists();
        $this->ensureDirectoryExists();
        $this->migrationGenerator = new MigrationGenerator($this->configData);
        $this->migrationGenerator->ensureDirectoryExists();
        $this->generateModel();
    }

    protected function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/backend/model.stub';
    }

    protected function getGeneratorDirectory(): string
    {
        return $this->modulePath . '/app/Models/';
    }

    protected function generateModel(): void
    {
        (new StubGenerator())->from($this->getStubPath(), true)
            ->to($this->getGeneratorDirectory(), true, true)
            ->withReplacers($this->getReplacers())
            ->as($this->modelName)
            ->replace(true)
            ->save();
    }

    protected function getReplacers(): array
    {
        return [
            'CLASS_NAMESPACE' => $this->modelNamespace,
            'CLASS_NAME' => $this->modelName,
            'USE_CLASSES' => $this->getUseClassesString(),
            'USES' => $this->getUses(),
            'FILLABLE' => $this->getFillable(),
            'OPTIONS' => $this->getOptions(),
            'RELATIONS' => $this->getRelations(),
            'SCOPES' => $this->getScopes(),
        ];
    }

    protected function getUseClassesString(): string
    {
        $useClasses = array_unique($this->getUseClasses());
        return count($useClasses) ? collect($useClasses)->implode(";\n") . ';' : '';
    }

    protected function getClassesNeedUsing(): array
    {
        return [
            'FileHelper' => 'use App\Helpers\File\Traits\FileHelper',
            'HasTranslations' => 'use Spatie\Translatable\HasTranslations',
            'SoftDeletes' => 'use Illuminate\Database\Eloquent\SoftDeletes',
            'ActivityLogHelper' => 'use App\Helpers\CrudHelpers\Traits\ActivityLogHelper',
        ];
    }
    
    protected function getUseClasses(): array
    {
        $classesNeedUsing = $this->getClassesNeedUsing();
        $useClasses = [];
        if (count($this->getFileFields())) $useClasses[] = $classesNeedUsing['FileHelper'];
        if (count($this->getBackendTranslatableFields())) $useClasses[] = $classesNeedUsing['HasTranslations'];
        if ($this->hasSoftDeletes()) $useClasses[] = $classesNeedUsing['SoftDeletes'];
        if ($this->hasAddLogs()) $useClasses[] = $classesNeedUsing['ActivityLogHelper'];
        foreach ($this->getModelRelations() as $relation) {
            $model = $relation['model'] ?? null;
            if (!$model) continue;
            if (Str::beforeLast($model, '\\') != $this->modelNamespace) $useClasses[] = "use {$model}";
        }
        return $useClasses;
    }

    protected function getUses(): string
    {
        $classesNeedUsing = $this->getClassesNeedUsing();
        $uses = collect($this->getUseClasses())->filter(fn ($use) => in_array($use, array_values($classesNeedUsing)))->map(fn ($use) => Str::afterLast($use, '\\') )->implode(", ");
        return $uses ? 'use ' . $uses . ";\n" : '';
    }

    protected function getFillable(): string
    {
        return collect($this->getFields())
        ->filter(fn($field) => !Field::isNotDatabase($field))
        ->map(function ($field, $name) {
            return "\n\t\t'$name'";
        })->implode(",");
    }

    protected function getOptions(): string
    {
        $options = [];
        $translatable = $this->getTranslatable();
        $casts = $this->getCasts();
        $fileCasts = $this->getFileCasts();
        $dates = $this->getDates();
        $booted = $this->getBooted();
        if ($translatable) $options[] = $translatable;
        if ($casts) $options[] = $casts;
        if ($fileCasts) $options[] = $fileCasts;
        if ($dates) $options[] = $dates;
        if ($booted) $options[] = $booted;
        return count($options) ? collect($options)->implode("\n") . "\n" : '';
    }
    
    protected function getCasts(): string
    {
        $fields = collect($this->getCastFields())
        ->filter(fn($field) => !Field::isNotDatabase($field))
        ->map(fn ($field, $name) => "\n\t\t'{$name}' => '{$field['cast']}'")->implode(',');
        return "\n\tprotected \$casts = [{$fields}\n\t];";
    }
    
    protected function getFileCasts(): string
    {
        $fileFields = $this->getFileFields();
        if (!count($fileFields)) return '';
        $hasSingle = collect($fileFields)->contains(fn ($field) => !Str::contains($field['type'], 'multi_'));
        $hasMulti = collect($fileFields)->contains(fn ($field) => Str::contains($field['type'], 'multi_'));
        $singlePath = $hasSingle ? "\n\t\t'single' => '{$this->modelNameSnake}_files'," : '';
        $multiPath = $hasMulti ? "\n\t\t'multi' => '{$this->modelNameSnake}_files'," : '';
        $filePaths = "\n\tpublic \$filePaths = [{$singlePath}{$multiPath}\n\t];\n";
        $fileCasts = collect($fileFields)->map(function ($field, $name) {
            $cast = Str::contains($field['type'], 'multi_') ? 'multi' : 'single';
            $url = $cast == 'multi' ? "Urls or \$this->{$name}StringUrls" : 'Url';
            return "\n\t\t'{$name}' => '{$cast}', // call \$this->{$name}{$url}";
        })->implode('');
        $fileCasts = "\n\tpublic \$fileCasts = [{$fileCasts}\n\t];";
        return $filePaths . $fileCasts;
    }
    
    protected function getTranslatable(): string
    {
        $translatableFields = $this->getBackendTranslatableFields();
        if (!count($translatableFields)) return '';
        $fields = collect($translatableFields)->map(function ($field, $name) {
            return "\n\t\t'{$name}'";   
        })->implode(',');
        return "\n\tpublic \$translatable = [{$fields}\n\t];";
    }
    
    protected function getDates(): string
    {
        $dateFields = $this->getDateFields();
        if (!count($dateFields)) return '';
        $fields = collect($dateFields)->map(function ($field, $name) {
            return "\n\t\t'{$name}'";   
        })->implode(',');
        return "\n\tprotected \$dates = [{$fields}\n\t];";
    }

    protected function getBooted(): string
    {
        $modelFieldsDelete = $this->getModelFieldsDelete();
        if (empty($modelFieldsDelete)) return '';
        $deleteMethod = $this->hasSoftDeletes() ? 'forceDeleted' : 'deleted';
        return "\n\tprotected static function booted()\n\t{\n\t\tstatic::{$deleteMethod}(function (\$model) {{$modelFieldsDelete}\n\t\t});\n\t}";
    }

    protected function getModelFieldsDelete(): string
    {
        $deleteFiles = collect($this->getFileFields())->map(function ($field, $name) {
            if (Str::contains($field['type'], 'multi_')) {
                return "\n\t\t\tforeach (\$model->{$name}Urls as \$file) {\n\t\t\t\t\$model->deleteFile(\$file['url'], \$model->filePaths['multi']);\n\t\t\t}";
            }
            return "\n\t\t\t\$model->deleteFile(\$model->{$name}, \$model->filePaths['single']);";
        })->implode('');
        $deleteRelations = collect($this->getModelRelations())
        ->filter(fn ($relation, $name) => ($relation['deleteRelation'] ?? false))
        ->map(function ($relation, $name) {
            return "\n\t\t\t\$model->{$name}()->delete();";
        })->implode('');
        return $deleteFiles . $deleteRelations;
    }

    protected function getScopes(): string
    {
        $fields = collect($this->getBooleanFields())->map(function ($field, $name) {
            $scopeName = Str::studly($name);
            return "\n\tpublic function scope{$scopeName}(\$query, \$value = true)\n\t{\n\t\treturn \$query->where('{$name}', \$value);\n\t}";
        });
        return count($fields) ? $fields->implode("\n") . "\n" : '';
    }

    protected function getRelations(): string
    {
        $relations = [];
        $index = 1;
        foreach ($this->getModelRelations() as $name => $relation) {
            $relation = $this->defineRelation($name, $relation, $index);
            if ($relation) $relations[] = $relation;
            $index++;
        }
        return count($relations) ? collect($relations)->implode("\n") . "\n" : '';
    }

    protected function defineRelation($name, $relation, $index): string
    {
        $relationHandler = $this->getRelationHandler($name, $relation, $index);
        return "\n\tpublic function {$name}()\n\t{\n\t\treturn \$this->{$relationHandler};\n\t}";
    }

    protected function getRelationHandler($name, $relation, $index): string
    {
        $model = isset($relation['model']) ? Str::afterLast($relation['model'], '\\') . '::class' : null;
        $normalRelations = ['belongsTo', 'hasOne', 'hasMany'];
        $morphRelations = ['morphOne', 'morphMany', 'morphToMany'];
        $type = in_array($relation['type'], $normalRelations) ? 'normal' : (in_array($relation['type'], $morphRelations) ? 'morph' : $relation['type']);
        $relation['model'] = $model;
        $relation['name'] = $name;
        if ($relation['addMigrationFile'] ?? false) $this->generateMigrationRelation($relation, $index);
        return $this->{"{$type}RelationHandler"}($relation);
    }

    // handle belongsTo, hasOne, hasMany relations
    protected function normalRelationHandler($relation): string
    {
        if (!$relation['model']) return '';
        $foreignKey = $relation['foreignKey'] ?? null;
        $localKey = $relation['localKey'] ?? null;
        $foreignKeyPrint = $foreignKey ? ", '{$foreignKey}'" : ($localKey ? ', null' : '');
        $localKeyPrint = $localKey ? ", '{$localKey}'" : '';
        return "{$relation['type']}({$relation['model']}{$foreignKeyPrint}{$localKeyPrint})";
    }

    protected function belongsToManyRelationHandler($relation): string
    {
        if (!$relation['model']) return '';
        $table = $relation['table'] ?? null;
        $foreignKey = $relation['foreignKey'] ?? null;
        $localKey = $relation['localKey'] ?? null;
        $tablePrint = $table ? ", '{$table}'" : ($foreignKey || $localKey ? ', null' : '');
        $foreignKeyPrint = $foreignKey ? ", '{$foreignKey}'" : ($localKey ? ', null' : '');
        $localKeyPrint = $localKey ? ", '{$localKey}'" : '';
        $relationLine = "{$relation['type']}({$relation['model']}{$tablePrint}{$foreignKeyPrint}{$localKeyPrint})";
        $pivots = isset($relation['pivot']) && is_array($relation['pivot']) ? collect($relation['pivot'])->keys()->map(fn ($pivot) => "'{$pivot}'")->implode(', ') : null;
        $pivotPrint = $pivots ? "->withPivot({$pivots})" : '';
        return $relationLine . $pivotPrint;
    }

    protected function morphRelationHandler($relation): string
    {
        if (!$relation['model']) return '';
        $name = $relation['morphName'] ?? Str::snake($this->makePolymorphic(Str::singular($relation['name'])));
        return "{$relation['type']}({$relation['model']}, '{$name}')";
    }

    protected function morphToRelationHandler($relation): string
    {
        return "{$relation['type']}()";
    }

    protected function morphedByManyRelationHandler($relation): string
    {
        if (!$relation['model']) return '';
        $name = $relation['morphName'] ?? $this->makePolymorphic($this->modelNameSnake);
        $table = $relation['table'] ?? null;
        $foreignKey = $relation['foreignKey'] ?? null;
        $localKey = $relation['localKey'] ?? null;
        $tablePrint = $table ? ", '{$table}'" : ($foreignKey || $localKey ? ', null' : '');
        $foreignKeyPrint = $foreignKey ? ", '{$foreignKey}'" : ($localKey ? ', null' : '');
        $localKeyPrint = $localKey ? ", '{$localKey}'" : '';
        return "{$relation['type']}({$relation['model']}, '{$name}'{$tablePrint}{$foreignKeyPrint}{$localKeyPrint})";
    }

    protected function generateMigrationRelation($relation, $index): void
    {
        $type = $relation['type'];
        $morphRelations = ['morphOne', 'morphMany', 'morphToMany'];
        if (in_array($type, ['morphOne', 'morphMany', 'morphToMany', 'belongsToMany'])) {
            $type = in_array($type, $morphRelations) ? 'morph' : $type;
            $this->{"{$type}MigrationHandler"}($relation, $index);
        }
    }

    protected function belongsToManyMigrationHandler($relation, $index): void
    {
        $tableNameConvention = $this->getBelongsToManyMigrationFileName(Str::before($relation['model'], '::class'));
        $tableName = $relation['table'] ?? $tableNameConvention;
        $fileName = $this->getRelationMigrationFileName($tableName, $this->belongsToManyMigrationName, $index);
        $replacers = $this->getBelongsToManyReplacers($tableName, $tableNameConvention, $relation['pivot'] ?? []);
        $this->generateMigrationRelationFile($fileName, $replacers);
    }

    protected function morphMigrationHandler($relation, $index): void
    {
        $tableName = $relation['table'] ?? Str::snake(Str::plural($this->makePolymorphic(Str::singular($relation['name']))));
        $fileName = $this->getRelationMigrationFileName($tableName, $this->morphToManyMigrationName, $index);
        $replacers = $this->getMorphReplacers($tableName, $relation, $relation['pivot'] ?? []);
        $this->generateMigrationRelationFile($fileName, $replacers);
    }

    protected function generateMigrationRelationFile($fileName, $replacers): void
    {
        (new StubGenerator())->from($this->migrationGenerator->getStubPath(), true)
            ->to($this->migrationGenerator->getGeneratorDirectory(), true, true)
            ->withReplacers($replacers)
            ->as($fileName)
            ->replace(true)
            ->save();
    }

    protected function generateMigrationName(string $name): string
    {
        return 'create_' . $name . '_table';
    }

    protected function getRelationMigrationFileName(string $tableName, $oldName = null, $index = 1): string
    {
        $migrationName = $this->generateMigrationName($tableName);
        $this->migrationGenerator->deleteOldMigration($migrationName, $oldName);
        return $this->migrationGenerator->generateMigrationFileName($migrationName, $oldName, ($index * 2));
    }

    protected function getBelongsToManyMigrationFileName($related): string
    {
        $segments = [$this->modelNameSnake, Str::snake(Str::singular($related))];
        sort($segments);
        return implode('_', $segments);
    }

    protected function handlePivots(array $pivots = []): array
    {
        $pivotFields = [];
        foreach ($pivots as $key => $pivot) {
            $params = $pivot['params'] ?? [];
            $appendParams = count($params) ? ', ' . collect($params)->map(fn($value) => json_encode($value))->implode(', ') : '';
            $definition = "\n\t\t\t\$table->{$pivot['type']}('{$key}'{$appendParams})";

            if (isset($pivot['nullable']) && $pivot['nullable'] === true) $definition .= '->nullable()';
            if (isset($pivot['default']) && $pivot['default'] !== null) {
                $default = is_bool($pivot['default']) || is_numeric($pivot['default']) ? json_encode($pivot['default']) : "'{$pivot['default']}'";
                $definition .= "->default({$default})";
            }
            $pivotFields[] = $definition . ';';
        }
        return $pivotFields;
    }

    protected function getBelongsToManyReplacers($tableName, $tableNameConvention, $pivots = []): array
    {
        [$key1, $key2] = explode('_', $tableNameConvention);
        $migrationFields = [
            "\$table->foreignId('{$key1}_id')->constrained()->cascadeOnDelete();",
            "\n\t\t\t\$table->foreignId('{$key2}_id')->constrained()->cascadeOnDelete();",
            ...$this->handlePivots($pivots),
        ];
        return [
            'TABLE_NAME' => $tableName,
            'FIELDS' => implode("", $migrationFields),
        ];
    }

    protected function getMorphReplacers($tableName, $relation, $pivots = []): array
    {
        $singularName = Str::snake(Str::singular($relation['name']));
        $foreignKeyName = $relation['morphName'] ?? $this->makePolymorphic($singularName);
        $migrationFields = [
            "\$table->foreignId('{$singularName}_id')->constrained()->cascadeOnDelete();",
            "\n\t\t\t\$table->morphs('{$foreignKeyName}');",
            ...$this->handlePivots($pivots),
        ];
        return [
            'TABLE_NAME' => $tableName,
            'FIELDS' => implode("", $migrationFields),
        ];
    }

}
