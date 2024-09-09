<?php

namespace W88\CrudSystem\Generators;

use W88\CrudSystem\Contracts\GeneratorInterface;
use Illuminate\Support\Facades\File;
use Touhidurabir\StubGenerator\Facades\StubGenerator;

class ModelGenerator implements GeneratorInterface
{
    protected array $config;
    protected string $modelName;
    protected string $modulePath;
    protected string $module;

    public function __construct(array $config, string $modelName, string $modulePath, string $module)
    {
        $this->config = $config;
        $this->modelName = $modelName;
        $this->modulePath = $modulePath;
        $this->module = $module;
    }

    public function generate(): void
    {
        $stubPath = $this->getStubPath();

        $this->ensureStubExists($stubPath);

        $modelDirectory = $this->getModelDirectory();
        $this->ensureDirectoryExists($modelDirectory);

        $this->generateModel($stubPath, $modelDirectory);
    }

    protected function getStubPath(): string
    {
        return base_path('vendor\w88\crud-system\src\stubs\model.stub');
    }

    protected function ensureStubExists(string $stubPath): void
    {
        if (!File::exists($stubPath)) {
            throw new \Exception("Stub file not found at path: {$stubPath}");
        }
    }

    protected function getModelDirectory(): string
    {
        return $this->modulePath . '/app/Models/';
    }

    protected function ensureDirectoryExists(string $directory): void
    {
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    protected function generateModel(string $stubPath, string $modelDirectory): void
    {
        StubGenerator::from($stubPath, true)
            ->to($modelDirectory, true, true)
            ->withReplacers($this->getReplacers())
            ->as($this->modelName)
            ->replace(true)
            ->save();
    }

    protected function getReplacers(): array
    {
        return [
            'NAMESPACE' => $this->module . '\app\Models',
            'CLASS' => $this->modelName,
            'FILLABLE' => $this->getFillableFields($this->config['table']['fields']),
        ];
    }

    protected function getFillableFields(array $fields): string
    {
        $fillableFields = array_map(fn($field) => "'{$field['name']}'", $fields);
        return "[\n            " . implode(",\n            ", $fillableFields) . "\n        ]";
    }
}
