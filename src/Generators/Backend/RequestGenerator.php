<?php

namespace W88\CrudSystem\Generators\Backend;


use W88\CrudSystem\Generators\Generator;
use Illuminate\Support\Facades\File;
use Touhidurabir\StubGenerator\Facades\StubGenerator;

class RequestGenerator extends Generator
{
 
    public function generate(): void
    {
        $this->ensureStubExists();
        $this->ensureDirectoryExists();
        $this->generateRequest();
    }

    protected function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/backend/request.stub';
    }

    protected function ensureStubExists(): void
    {
        $stubPath = $this->getStubPath();
        if (!File::exists($stubPath)) {
            throw new \Exception("Stub file not found at path: {$stubPath}");
        }
    }

    protected function getRequestDirectory(): string
    {
        return "{$this->modulePath}/app/Http/Requests/{$this->versionNamespace}";
    }

    protected function ensureDirectoryExists(): void
    {
        $directory = $this->getRequestDirectory();
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    protected function generateRequest(): void
    {
        StubGenerator::from($this->getStubPath(), true)
            ->to($this->getRequestDirectory())
            ->withReplacers($this->getReplacers())
            ->replace(true)
            ->as($this->getRequestName())
            ->save();
    }

    protected function getReplacers(): array
    {
        return [
            'CLASS_NAMESPACE' => $this->getRequestNamespace(),
            'CLASS_NAME' => $this->getRequestName(),
            'TRANSLATION_PATH' => "{$this->moduleNameSnake}::view.{$this->modelNameSnake}_crud.validation",
            'RULES' => $this->getRules(),
        ];
    }

    protected function getRules(): string
    {
        return collect($this->getFields())->map(fn($field, $name) => $this->getFieldValidationRule($name, $field))->implode(',');
    }

    protected function getFieldValidationRule(string $name, array $field): string
    {
        if (!isset($field['validation'])) return '';
        $rule = is_array($field['validation']) ? $this->handleArrayValidationRule($field['validation']) : "'{$field['validation']}'";
        return "\n\t\t\t'$name' => $rule";
    }

    public function handleArrayValidationRule(array $validation): string
    {
        return '[' . collect($validation)->map(fn($rule) => "\n\t\t\t\t" . ($this->isPhpCode($rule) ? $rule : "'{$rule}'"))->implode(',') . "\n\t\t\t]";
    }
    
}
