<?php

namespace W88\CrudSystem\Generators\Backend;

use W88\CrudSystem\Generators\Generator;
use Illuminate\Support\Facades\File;
use Touhidurabir\StubGenerator\Facades\StubGenerator;
use W88\CrudSystem\Field;

class SeederGenerator extends Generator
{

    protected $seederOption;

    public function generate(): void
    {
        $this->seederOption = $this->getSeederOption();
        if (!$this->seederOption) return;
        $this->ensureStubExists();
        $this->ensureDirectoryExists();
        $this->generateSeeder();
    }

    protected function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/backend/seeder.stub';
    }

    protected function getSeederDirectory(): string
    {
        return "{$this->modulePath}/database/seeders";
    }

    protected function getSeederName(): string
    {
        return $this->modelName . 'Seeder';
    }

    protected function getSeederNamespace(): string
    {
        return "{$this->moduleNamespace}\database\seeders";
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
        $directory = $this->getSeederDirectory();
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    protected function generateSeeder(): void
    {
        StubGenerator::from($this->getStubPath(), true)
            ->to($this->getSeederDirectory())
            ->withReplacers($this->getReplacers())
            ->replace(true)
            ->as($this->getSeederName())
            ->save();
    }

    protected function getReplacers(): array
    {
        return [
            'CLASS_NAME' => $this->getSeederName(),
            'CLASS_NAMESPACE' => $this->getSeederNamespace(),
            'MODEL_NAME' => $this->modelName,
            'MODEL_NAMESPACE' => $this->modelNamespace,
            'FIELDS' => $this->getFieldsTemplate(),
            'COUNT' => $this->seederOption['count'] ?? 10,
        ];
    }

    protected function getFieldsTemplate(): string
    {
        return collect($this->getFields())->map(function ($field, $name) {
            $field['name'] = $name;
            $value = Field::getSeederType($field);
            return "\n\t\t\t\t'{$name}' => {$value},";
        })->join('');
    }

}
