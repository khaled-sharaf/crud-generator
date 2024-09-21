<?php

namespace W88\CrudSystem\Generators\Backend;

use W88\CrudSystem\Generators\Generator;
use Illuminate\Support\Facades\File;
use Touhidurabir\StubGenerator\StubGenerator;
use Illuminate\Support\Str;
use W88\CrudSystem\Field;

class ConstantGenerator extends Generator
{

    public function generate(): void
    {
        $this->ensureStubExists();
        $this->ensureDirectoryExists();
        $this->generateConstant();
    }

    protected function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/backend/constant.stub';
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
        return "{$this->modulePath}/app/Constants/{$this->modelName}";
    }

    protected function ensureDirectoryExists(): void
    {
        $directory = $this->getGeneratorDirectory();
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    protected function generateConstant(): void
    {
        foreach ($this->getConstantFields() as $name => $field) {
            $field['name'] = Str::studly($name);
            (new StubGenerator)->from($this->getStubPath(), true)
                ->to($this->getGeneratorDirectory())
                ->withReplacers($this->getReplacers($field))
                ->replace(true)
                ->as($field['name'])
                ->save();
        }
    }

    protected function getReplacers($field): array
    {
        $options = $this->formatOptions($field['name'], Field::getOptions($field));
        return [
            'CLASS_NAMESPACE' => $this->getConstantNamespace(),
            'CLASS_NAME' => $field['name'],
            'CONSTANTS' => $this->getConstantsTemplate($options),
            'CONSTANTS_LIST' => $this->getConstantsListTemplate($options),
        ];
    }

    protected function getConstantsTemplate(array $options): string
    {
        $constants = '';
        foreach ($options as $option) {
            $constants .= "\n\tconst {$option['name']} = {$option['value']};";
        }
        return $constants;
    }

    protected function getConstantsListTemplate(array $options): string
    {
        $constants = '';
        foreach ($options as $option) {
            $constants .= "\n\t\t\tself::{$option['name']} => {$option['label']},";
        }
        return $constants;
    }

    protected function formatOptions(string $name, array $options): array
    {
        return collect($options)->map(function ($value, $key) use ($name) {
            $keyTrans = strtolower(Str::snake($key));
            $constantName = strtolower(Str::snake($name));
            return [
                'name' => strtoupper(Str::snake($key)),
                'label' => "__('{$this->moduleNameSnake}::view.{$this->modelNameSnake}_crud.constants.{$constantName}.{$keyTrans}')",
                'value' => json_encode($value['value'] ?? $key),
            ];
        })->toArray();
    }
}
