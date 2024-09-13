<?php

namespace W88\CrudSystem\Generators\Backend;

use W88\CrudSystem\Contracts\GeneratorInterface;
use Illuminate\Support\Facades\File;
use Touhidurabir\StubGenerator\Facades\StubGenerator;
use Illuminate\Support\Str;

class ControllerGenerator implements GeneratorInterface
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

        $controllerNamespace = $this->getControllerNamespace();
        $controllerDirectory = $this->getControllerDirectory();

        $this->ensureDirectoryExists($controllerDirectory);

        $this->generateController($stubPath, $controllerDirectory, $controllerNamespace);
    }

    protected function getStubPath(): string
    {
        return __DIR__ . '/../../../backend/stubs/controller.stub';
    }

    protected function ensureStubExists(string $stubPath): void
    {
        if (!File::exists($stubPath)) {
            throw new \Exception("Stub file not found at path: {$stubPath}");
        }
    }

    protected function getControllerNamespace(): string
    {
        return $this->module . '\app\Http\Controllers\\' . Str::studly($this->version);
    }

    protected function getControllerDirectory(): string
    {
        return $this->modulePath . '/app/Http/Controllers/' . Str::studly($this->version);
    }

    protected function ensureDirectoryExists(string $directory): void
    {
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    protected function generateController(string $stubPath, string $controllerDirectory, string $controllerNamespace): void
    {
        StubGenerator::from($stubPath, true)
            ->to($controllerDirectory)
            ->withReplacers($this->getReplacers($controllerNamespace))
            ->replace(true)
            ->as($this->modelName . 'Controller')
            ->save();
    }

    protected function getReplacers(string $controllerNamespace): array
    {
        return [
            'CLASS_NAMESPACE' => $controllerNamespace,
            'CLASS' => $this->modelName . 'Controller',
            'LOWER_NAME' => strtolower($this->modelName),
            'MODEL' => $this->modelName,
            'MODEL_NAMESPACE' => $this->module . '\app\Models',
        ];
    }
}
