<?php

namespace W88\CrudSystem\Generators\Backend;

use W88\CrudSystem\Generators\Generator;
use Illuminate\Support\Facades\File;
use Touhidurabir\StubGenerator\Facades\StubGenerator;
use W88\CrudSystem\Facades\Field;
use Illuminate\Support\Str;
use W88\CrudSystem\Traits\BackendHelpersTrait;

class MigrationGenerator extends Generator
{
    use BackendHelpersTrait;
    
    protected $migrationName;

    public function generate(): void
    {
        $this->ensureStubExists();
        $this->ensureDirectoryExists();
        $this->deleteOldMigration($this->generateMigrationName(), $this->migrationName);
        $this->generateMigrationFile();
    }

    public function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/backend/migration.stub';
    }

    protected function generateMigrationName(): string
    {
        return 'create_' . $this->modelNameSnakePlural . '_table';
    }

    public function generateMigrationFileName(string $name, string $oldMigrationName = null, int $padding = 0): string
    {
        $time = intval(now()->format('His')) + $padding;
        return ($oldMigrationName ?? $this->migrationName) ?? now()->format('Y_m_d_') . "{$time}_$name";
    }

    public function getGeneratorDirectory(): string
    {
        return $this->modulePath . '/database/migrations';
    }

    public function deleteOldMigration(string $name, string &$oldMigrationFileName = null): void
    {
        foreach (File::files($this->getGeneratorDirectory()) as $file) {
            if (Str::endsWith($file->getFilename(), $name . '.php')) {
                $oldMigrationFileName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                File::delete($file->getPathname());
            }
        }
    }

    protected function generateMigrationFile(): void
    {
        StubGenerator::from($this->getStubPath(), true)
            ->to($this->getGeneratorDirectory(), true, true)
            ->withReplacers($this->getReplacers())
            ->as($this->generateMigrationFileName($this->generateMigrationName()))
            ->replace(true)
            ->save();
    }

    protected function getReplacers(): array
    {
        return [
            'TABLE_NAME' => $this->modelNameSnakePlural,
            'FIELDS' => $this->getMigrationFields(),
        ];
    }

    protected function getMigrationFields(): string
    {
        $migrationFields = ['$table->id();', ...collect($this->getFields())->map(fn($field, $name) => $this->generateFieldDefinition($name, $field))->toArray()];
        if ($this->hasSoftDeletes()) $migrationFields[] = '$table->softDeletes();';
        $migrationFields[] = '$table->timestamps();';
        return implode("\n\t\t\t", $migrationFields);
    }

    protected function generateFieldDefinition(string $name, array $field): string
    {
        $type = Field::getMigrationType($field);
        $definition = "\$table->{$type}('{$name}')";
        if (isset($field['nullable']) && $field['nullable'] === true) $definition .= '->nullable()';
        if (isset($field['default']) && $field['default'] !== null) {
            $default = is_bool($field['default']) || is_numeric($field['default']) ? json_encode($field['default']) : "'{$field['default']}'";
            $definition .= "->default({$default})";
        }
        if (isset($field['relation'])) {
            $definition .= $this->generateRelationDefinition($name, $field);
        }
        return $definition . ';';
    }

    protected function generateRelationDefinition(string $name, array $field): string
    {
        $relation = $field['relation'];
        $definition = '';
        $isConstrained = Field::isRelationConstrained($field);
        if ($isConstrained && Field::isNullable($field) && !isset($relation['onDelete'])) $relation = ['onDelete' => 'set null'];
        $foreignKey = isset($field['relation']['foreignKey']) ? $field['relation']['foreignKey'] : null;
        $table = isset($field['relation']['table']) ? $field['relation']['table'] : null;
        $tableConstraint = $table ? "table: '{$relation['table']}'" : '';
        $column = $foreignKey ? "column: '{$foreignKey}'" : '';
        $constrainedParams = $tableConstraint && $column ? "{$tableConstraint}, {$column}" : ($tableConstraint ? $tableConstraint : ($column ? $column : ''));
        if ($isConstrained) {
            $definition .= "->constrained({$constrainedParams})";
        } else if ($table) {
            $foreignKey = $foreignKey ?? 'id';
            $definition .= ";\n\t\t\t\$table->foreign('{$name}')->references('{$foreignKey}')->on('{$table}')";
        }
        if (($isConstrained || $table) && isset($relation['onUpdate']) && $relation['onUpdate'] !== null) $definition .= "->onUpdate('{$relation['onUpdate']}')";
        if (($isConstrained || $table) && isset($relation['onDelete']) && $relation['onDelete'] !== null) $definition .= "->onDelete('{$relation['onDelete']}')";
        return $definition;
    }
}
