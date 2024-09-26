<?php

namespace W88\CrudSystem\Generators\Frontend;

use W88\CrudSystem\Generators\Generator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use W88\CrudSystem\Facades\Crud;
use W88\CrudSystem\Traits\FrontendHelpersTrait;

class RouteGenerator extends Generator
{
    use FrontendHelpersTrait;

    public function generate(): void
    {
        $this->ensureStubExists();
        $this->ensureDirectoryExists();
        $this->generateResource();
    }

    protected function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/backend/resource.stub';
    }

    protected function ensureStubExists(): void
    {
        $stubPath = $this->getStubPath();
        if (!File::exists($stubPath)) {
            throw new \Exception("Stub file not found at path: {$stubPath}");
        }
    }

    protected function getGeneratorDirectory(): string
    {
        return "{$this->modulePath}/app/Resources/{$this->versionNamespace}";
    }

    protected function getLocalResourceNamespace(): string
    {
        return $this->getResourceNamespace();
    }

    protected function ensureDirectoryExists(): void
    {
        $directory = $this->getGeneratorDirectory();
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    protected function generateResource(): void
    {
        StubGenerator::from($this->getStubPath(), true)
            ->to($this->getGeneratorDirectory())
            ->withReplacers($this->getReplacers())
            ->replace(true)
            ->as($this->getResourceName())
            ->save();
    }

    protected function getReplacers(): array
    {
        return [
            'CLASS_NAMESPACE' => $this->getLocalResourceNamespace(),
            'CLASS_NAME' => $this->getResourceName(),
            'FIELDS' => $this->getFieldsData(),
        ];
    }

    protected function getTimestampsFields(): string
    {
        return ",\n\t\t\t'created_at' => formatDate(\$this->created_at),\n\t\t\t'updated_at' => formatDate(\$this->updated_at)";
    }

    protected function getFieldsData(): string
    {
        return collect($this->getNotHiddenFields())->map(function ($field, $name) {
            return "'$name' => \$this->{$name}";
        })->implode(",\n\t\t\t") . $this->getTimestampsFields();
    }
    
}
