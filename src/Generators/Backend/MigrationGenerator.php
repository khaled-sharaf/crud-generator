<?php

namespace W88\CrudSystem\Generators\Backend;

use Illuminate\Support\Facades\File;
use Touhidurabir\StubGenerator\Facades\StubGenerator;
use W88\CrudSystem\Field;
use W88\CrudSystem\Generators\Generator;

class MigrationGenerator extends Generator
{

    protected $migrationName;

    public function generate(): void
    {
        $this->ensureStubExists();
        $this->ensureDirectoryExists();
        $this->deleteOldMigration();
        $this->generateMigrationFile();
    }

    protected function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/backend/migration.stub';
    }

    protected function ensureStubExists(): void
    {
        $stubPath = $this->getStubPath();
        if (!File::exists($stubPath)) {
            throw new \Exception("Stub file not found at path: {$stubPath}");
        }
    }

    protected function generateMigrationNameWithTimestamp(): string
    {
        return 'create_' . $this->modelNameSnakePlural . '_table';
    }

    protected function generateMigrationName(): string
    {
        return $this->migrationName ?? now()->format('Y_m_d_His') . '_' . $this->generateMigrationNameWithTimestamp();
    }

    protected function getMigrationDirectory(): string
    {
        return $this->modulePath . '/database/migrations';
    }

    protected function ensureDirectoryExists(): void
    {
        $directory = $this->getMigrationDirectory();
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    protected function deleteOldMigration(): void
    {
        foreach (File::files($this->getMigrationDirectory()) as $file) {
            if (strpos($file->getFilename(), $this->generateMigrationNameWithTimestamp() . '.php') !== false) {
                $this->migrationName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                File::delete($file->getPathname());
                break;
            }
        }
    }

    protected function generateMigrationFile(): void
    {
        StubGenerator::from($this->getStubPath(), true)
            ->to($this->getMigrationDirectory(), true, true)
            ->withReplacers($this->getReplacers())
            ->as($this->generateMigrationName())
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
        $migrationFields = collect($this->getFields())->map(fn($field, $name) => $this->generateFieldDefinition($name, $field))->toArray();
        if ($this->hasSoftDeletes()) $migrationFields[] = '$table->softDeletes();';
        if ($this->hasTimestamps()) $migrationFields[] = '$table->timestamps();';
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
        $isConstrained = isset($relation['constrained']) && $relation['constrained'] === true;
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
