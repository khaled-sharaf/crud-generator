<?php

namespace W88\CrudSystem\Generators\Backend;


use W88\CrudSystem\Generators\Generator;
use Illuminate\Support\Facades\File;
use Touhidurabir\StubGenerator\Facades\StubGenerator;
use Illuminate\Support\Str;

class RequestGenerator extends Generator
{
 
    public function generate(): void
    {
        if (!$this->checkApiRoute('create') && !$this->checkApiRoute('edit')) return;
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

    protected function getGeneratorDirectory(): string
    {
        return "{$this->modulePath}/app/Http/Requests/{$this->versionNamespace}";
    }

    protected function ensureDirectoryExists(): void
    {
        $directory = $this->getGeneratorDirectory();
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    protected function generateRequest(): void
    {
        StubGenerator::from($this->getStubPath(), true)
            ->to($this->getGeneratorDirectory())
            ->withReplacers($this->getReplacers())
            ->replace(true)
            ->as($this->getRequestName())
            ->save();
    }

    protected function getReplacers(): array
    {
        $rules = $this->getRules();
        return [
            'CLASS_NAMESPACE' => $this->getRequestNamespace(),
            'CLASS_NAME' => $this->getRequestName(),
            'TRANSLATION_PATH' => "{$this->moduleNameSnake}::view.{$this->modelNameSnake}_crud.validation",
            'RULES' => $rules,
            'USE_CLASSES' => $this->getUseClasses($rules),
        ];
    }

    protected function getUseClasses(string $rules): string
    {
        if (strpos($rules, 'Rule::') !== false || strpos($rules, 'new Rule') !== false) {
            return "use Illuminate\Validation\Rule;\n";
        }
        return '';
    }

    protected function getRules(): string
    {
        return collect($this->getNotHiddenFields())->map(fn($field, $name) => $this->getFieldValidationRule($name, $field))->implode(',');
    }

    protected function getFieldValidationRule(string $name, array $field): string
    {
        $validations = collect($field)->filter(fn($value, $key) => Str::startsWith($key, 'validation'))->map(function($value, $key) use ($name) {
            $key = str_replace('validation', $name, $key);
            $rule = is_array($value) ? $this->handleArrayValidationRule($value) : "'{$value}'";
            return "\n\t\t\t'{$key}' => $rule";
        });
        $defaultValidation = "\n\t\t\t'$name' => 'nullable'";
        return count($validations) ? $validations->implode(',') : $defaultValidation;
    }

    public function handleArrayValidationRule(array $validation): string
    {
        return '[' . collect($validation)->map(fn($rule) => "\n\t\t\t\t" . ($this->isPhpCode($rule) ? $rule : "'{$rule}'"))->implode(',') . "\n\t\t\t]";
    }
    
}
