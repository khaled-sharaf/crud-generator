<?php

namespace W88\CrudSystem\Generators\Backend;


use W88\CrudSystem\Generators\Generator;
use Illuminate\Support\Facades\File;
use Touhidurabir\StubGenerator\Facades\StubGenerator;

class ResourceGenerator extends Generator
{

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

    protected function getResourceDirectory(): string
    {
        return "{$this->modulePath}/app/Resources/{$this->versionNamespace}";
    }

    protected function ensureDirectoryExists(): void
    {
        $directory = $this->getResourceDirectory();
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    protected function generateResource(): void
    {
        StubGenerator::from($this->getStubPath(), true)
            ->to($this->getResourceDirectory())
            ->withReplacers($this->getReplacers())
            ->replace(true)
            ->as($this->getResourceName())
            ->save();
    }

    protected function getReplacers(): array
    {
        return [
            'CLASS_NAMESPACE' => $this->getResourceNamespace(),
            'CLASS_NAME' => $this->getResourceName(),
            'FIELDS' => $this->getFieldsData(),
        ];
    }

    protected function getFieldsData(): string
    {
        $timestamps = ",\n\t\t\t'created_at' => formatDate(\$this->created_at),\n\t\t\t'updated_at' => formatDate(\$this->updated_at)";
        return collect($this->getFields())->map(function ($field, $name) {
            return "'$name' => \$this->{$name}";
        })->implode(",\n\t\t\t") . $timestamps;
    }

}
