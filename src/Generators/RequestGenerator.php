<?php

namespace W88\CrudSystem\Generators;

use W88\CrudSystem\Contracts\GeneratorInterface;
use Illuminate\Support\Facades\File;
use Touhidurabir\StubGenerator\Facades\StubGenerator;
use Illuminate\Support\Str;

class RequestGenerator implements GeneratorInterface
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

        $requestNamespace = $this->getRequestNamespace();
        $requestDirectory = $this->getRequestDirectory();

        $this->ensureDirectoryExists($requestDirectory);

        $this->generateRequest($stubPath, $requestDirectory, $requestNamespace);
    }

    protected function getStubPath(): string
    {
        return base_path('W88/CrudSystem/stubs/request.stub');
    }

    protected function ensureStubExists(string $stubPath): void
    {
        if (!File::exists($stubPath)) {
            throw new \Exception("Stub file not found at path: {$stubPath}");
        }
    }

    protected function getRequestNamespace(): string
    {
        return $this->module . '\app\Http\Requests\\' . Str::studly($this->version);
    }

    protected function getRequestDirectory(): string
    {
        return $this->modulePath . '/app/Http/Requests/' . Str::studly($this->version);
    }

    protected function ensureDirectoryExists(string $directory): void
    {
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    protected function generateRequest(string $stubPath, string $requestDirectory, string $requestNamespace): void
    {
        StubGenerator::from($stubPath, true)
            ->to($requestDirectory)
            ->withReplacers($this->getReplacers($requestNamespace))
            ->replace(true)
            ->as($this->modelName . 'Request')
            ->save();
    }

    protected function getReplacers(string $requestNamespace): array
    {
        return [
            'NAMESPACE' => $requestNamespace,
            'CLASS' => $this->modelName . 'Request',
        ];
    }
}
