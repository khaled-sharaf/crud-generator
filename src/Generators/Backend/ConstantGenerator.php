<?php

namespace Khaled\CrudSystem\Generators\Backend;

use Khaled\CrudSystem\Generators\BackendGenerator;
use Touhidurabir\StubGenerator\StubGenerator;
use Illuminate\Support\Str;
use Khaled\CrudSystem\Facades\Field;

class ConstantGenerator extends BackendGenerator
{
    public function checkBeforeGenerate(): bool
    {
        return count($this->getConstantFields());
    }

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

    protected function getGeneratorDirectory(): string
    {
        return "{$this->modulePath}/app/Constants/{$this->modelName}";
    }

    protected function generateConstant(): void
    {
        foreach ($this->getConstantFields() as $name => $field) {
            $field['name'] = Str::studly($name);
            $fileName = $this->getConstantName($field);
            $field['fileName'] = $fileName;
            (new StubGenerator)->from($this->getStubPath(), true)
                ->to($this->getGeneratorDirectory())
                ->withReplacers($this->getReplacers($field))
                ->replace(true)
                ->as($fileName)
                ->save();
        }
    }

    protected function getReplacers($field): array
    {
        $options = $this->formatOptions($field['name'], Field::getOptions($field));
        return [
            'CLASS_NAMESPACE' => $this->getConstantNamespace(),
            'CLASS_NAME' => $field['fileName'],
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
            $key = strtolower($key);
            $keyTrans = Str::snake($key);
            $constantName = strtolower(Str::snake($name));
            $value = $value['value'] ?? $key;
            $value = is_numeric($value) ? intval($value) : $value;
            return [
                'name' => strtoupper($keyTrans),
                'label' => "__('{$this->moduleNameSnake}::view.{$this->modelNameSnake}_crud.constants.{$constantName}.{$keyTrans}')",
                'value' => json_encode($value),
            ];
        })->toArray();
    }
}
