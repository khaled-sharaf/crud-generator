<?php

namespace W88\CrudSystem\Generators;

use W88\CrudSystem\Contracts\GeneratorInterface;
use Illuminate\Support\Facades\File;
use Touhidurabir\StubGenerator\Facades\StubGenerator;
use Illuminate\Support\Str;

class ResourceGenerator implements GeneratorInterface
{
    protected array $config;
    protected string $modelName;
    protected string $modulePath;
    protected ?string $module;
    protected ?string $version;

    public function __construct(array $config, string $modelName, string $modulePath, ?string $module = null, ?string $version = null)
    {
        $this->config = $config;
        $this->modelName = $modelName;
        $this->modulePath = $modulePath;
        $this->module = $module;
        $this->version = $version;
    }

    public function generate(): void
    {
        $stubPath = $this->getStubPath();

        $this->ensureStubExists($stubPath);

        $resourceNamespace = $this->getResourceNamespace();
        $resourceDirectory = $this->getResourceDirectory();

        $this->ensureDirectoryExists($resourceDirectory);

        $this->generateResource($stubPath, $resourceDirectory, $resourceNamespace);
    }

    protected function getStubPath(): string
    {
        return base_path('W88/CrudSystem/stubs/resource.stub');
    }

    protected function ensureStubExists(string $stubPath): void
    {
        if (!File::exists($stubPath)) {
            throw new \Exception("Stub file not found at path: {$stubPath}");
        }
    }

    protected function getResourceNamespace(): string
    {
        return $this->module . '\app\Http\Resources\\' . Str::studly($this->version);
    }

    protected function getResourceDirectory(): string
    {
        return $this->modulePath . '/app/Http/Resources/' . Str::studly($this->version);
    }

    protected function ensureDirectoryExists(string $directory): void
    {
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    protected function generateResource(string $stubPath, string $resourceDirectory, string $resourceNamespace): void
    {
        StubGenerator::from($stubPath, true)
            ->to($resourceDirectory)
            ->withReplacers($this->getReplacers($resourceNamespace))
            ->replace(true)
            ->as($this->modelName . 'Resource')
            ->save();
    }

    protected function getReplacers(string $resourceNamespace): array
    {
        return [
            'NAMESPACE' => $resourceNamespace,
            'CLASS' => $this->modelName . 'Resource',
        ];
    }
}
