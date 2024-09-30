<?php

namespace W88\CrudSystem\Generators\Frontend;

use W88\CrudSystem\Generators\FrontendGenerator;
use Touhidurabir\StubGenerator\StubGenerator;
use Illuminate\Support\Str;
use W88\CrudSystem\Facades\Field;

class LookupGenerator extends FrontendGenerator
{

    public function generate(): void
    {
        $this->ensureStubExists();
        $this->ensureDirectoryExists();
        $this->generateConstant();
    }

    protected function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/frontend/lookup.stub';
    }

    protected function getGeneratorDirectory(): string
    {
        return "{$this->getFrontendModulePath()}/lookups/{$this->modelNameCamel}";
    }

    protected function generateConstant(): void
    {
        foreach ($this->getFieldsHasLookupFrontend() as $name => $field) {
            $lookupFile = $this->getLookupFile($name);
            (new StubGenerator)->from($this->getStubPath(), true)
                ->to($this->getGeneratorDirectory())
                ->withReplacers($this->getReplacers($field))
                ->replace(true)
                ->as($lookupFile)
                ->ext('js')
                ->save();
        }
    }

    protected function getReplacers($field): array
    {
        $options = $this->formatOptions($field['name'], Field::getOptions($field));
        return [
            'CONSTANTS' => $this->getConstantsTemplate($options),
        ];
    }

    protected function getConstantsTemplate(array $options): string
    {
        $constants = [];
        foreach ($options as $option) {
            $constants[] = "\n\t{\n\t\tkey: '{$option['key']}',\n\t\tvalue: {$option['value']},\n\t\tlabel: {$option['label']}\n\t}";
        }
        return implode(",", $constants);
    }

    protected function formatOptions(string $name, array $options): array
    {
        return collect($options)->map(function ($value, $key) use ($name) {
            $keyTrans = strtolower(Str::snake($key));
            $constantName = strtolower(Str::snake($name));
            $label = $this->getLangPath("lookups.{$constantName}.{$keyTrans}");
            return [
                'key' => Str::studly($key),
                'value' => json_encode($value['value'] ?? $key),
                'label' => "i18n.global.t('{$label}')",
            ];
        })->toArray();
    }
}
