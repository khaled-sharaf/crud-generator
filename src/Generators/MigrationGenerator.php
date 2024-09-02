<?php

namespace W88\CrudSystem\Generators;

use W88\CrudSystem\Contracts\GeneratorInterface;
use Illuminate\Support\Facades\File;
use Touhidurabir\StubGenerator\Facades\StubGenerator;

class MigrationGenerator implements GeneratorInterface
{
    protected array $config;
    protected string $modelName;
    protected string $modulePath;

    public function __construct(array $config, string $modelName, string $modulePath)
    {
        $this->config = $config;
        $this->modelName = $modelName;
        $this->modulePath = $modulePath;
    }

    public function generate(): void
    {
        $stubPath = $this->getStubPath();

        $this->ensureStubExists($stubPath);

        $migrationDir = $this->getMigrationDirectory();
        $migrationName = $this->generateMigrationName();

        $this->ensureDirectoryExists($migrationDir);

        $this->generateMigrationFile($stubPath, $migrationDir, $migrationName);
    }

    protected function getStubPath(): string
    {
        return base_path('W88/CrudSystem/stubs/migration.stub');
    }

    protected function ensureStubExists(string $stubPath): void
    {
        if (!File::exists($stubPath)) {
            throw new \Exception("Stub file not found at path: {$stubPath}");
        }
    }

    protected function generateMigrationName(): string
    {
        return now()->format('Y_m_d_His') . '_create_' . strtolower($this->modelName) . '_table';
    }

    protected function getMigrationDirectory(): string
    {
        return $this->modulePath . '/Database/Migrations';
    }

    protected function ensureDirectoryExists(string $directory): void
    {
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    protected function generateMigrationFile(string $stubPath, string $migrationDir, string $migrationName): void
    {
        StubGenerator::from($stubPath, true)
            ->to($migrationDir, true, true)
            ->withReplacers($this->getReplacers())
            ->as($migrationName)
            ->replace(true)
            ->save();
    }

    protected function getReplacers(): array
    {
        return [
            'TABLE' => strtolower($this->modelName),
            'FIELDS' => $this->getMigrationFields($this->config['table']['fields'], $this->config['table']['soft_deletes']),
        ];
    }

    protected function getMigrationFields(array $fields, bool $softDeletes): string
    {
        $migrationFields = array_map(
            fn($field) => $this->generateFieldDefinition($field),
            $fields
        );

        if ($softDeletes) {
            $migrationFields[] = "\$table->softDeletes();";
        }

        return implode("\n            ", $migrationFields);
    }

    protected function generateFieldDefinition(array $field): string
    {
        $definition = "\$table->{$field['type']}('{$field['name']}')";

        if ($field['nullable']) {
            $definition .= '->nullable()';
        }
        if ($field['default'] !== null) {
            $definition .= "->default('{$field['default']}')";
        }

        return $definition . ';';
    }
}
