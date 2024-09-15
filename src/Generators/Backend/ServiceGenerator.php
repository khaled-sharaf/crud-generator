<?php

namespace W88\CrudSystem\Generators\Backend;

use W88\CrudSystem\Generators\Generator;
use Illuminate\Support\Facades\File;
use Touhidurabir\StubGenerator\Facades\StubGenerator;

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
            'MODEL' => $this->modelName,
            'MODEL_LOWER' => $this->modelNameCamel,
            'MODEL_NAMESPACE' => $this->modelNamespace(),
            'FILTERS' => $this->getFilters(),
            'HANDLE_FIELDS_WHEN_CREATE' => $this->handleFieldsWhenCreate(),
            'HANDLE_FIELDS_WHEN_UPDATE' => $this->handleFieldsWhenUpdate(),
        ];
    }

    protected function getFilters(): string
    {
        $filters = '';
        if ($this->hasSoftDeletes()) {
            $filters .= "\App\Filters\Boolean\Trashed::class,\n\t\t\t";
        }
        if ($this->hasActivationRoute()) {
            $filters .= "new \App\Filters\Boolean\ToggleBoolean('is_active'),\n\t\t\t";
        }
        return $filters;
    }

    protected function handleFieldsWhenCreate(): string
    {
        return '';
        return "\n\t\t";
    }

    protected function handleFieldsWhenUpdate(): string
    {
        return '';
        return "\n\t\t";
    }
}
