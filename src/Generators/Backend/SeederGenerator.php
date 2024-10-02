<?php

namespace W88\CrudSystem\Generators\Backend;

use W88\CrudSystem\Generators\BackendGenerator;
use Illuminate\Support\Facades\File;
use Touhidurabir\StubGenerator\StubGenerator;
use W88\CrudSystem\Facades\Field;

class SeederGenerator extends BackendGenerator
{
    
    protected $moduleSeederFileName;

    public function checkBeforeGenerate(): bool
    {
        return $this->hasSeeder();
    }
    
    public function generate(): void
    {
        $this->moduleSeederFileName = "{$this->moduleName}DatabaseSeeder";
        $this->ensureStubExists();
        $this->ensureDirectoryExists();
        $this->ensureSeederModuleFileExists();
        $this->generateSeeder();
        $this->addSeederToModuleSeeder();
    }

    protected function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/backend/seeder.stub';
    }

    protected function getModuleStubPath(): string
    {
        return __DIR__ . '/../../stubs/backend/moduleSeeder.stub';
    }

    protected function getGeneratorDirectory(): string
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

    protected function ensureSeederModuleFileExists(): void
    {
        $filePath = $this->getGeneratorDirectory() . "/{$this->moduleSeederFileName}.php";
        if (!File::exists($filePath)) {
            $this->createSeederModuleFile($this->moduleSeederFileName);
        }
    }
    
    protected function createSeederModuleFile(): void
    {
        (new StubGenerator)->from($this->getModuleStubPath(), true)
            ->to($this->getGeneratorDirectory())
            ->withReplacers([
                'CLASS_NAME' => $this->moduleSeederFileName,
                'CLASS_NAMESPACE' => $this->getSeederNamespace(),
            ])
            ->replace(true)
            ->as($this->moduleSeederFileName)
            ->save();
    }

    protected function generateSeeder(): void
    {
        (new StubGenerator)->from($this->getStubPath(), true)
            ->to($this->getGeneratorDirectory())
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
            'COUNT' => 10,
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

    protected function addSeederToModuleSeeder(): void
    {
        $filePath = "{$this->getGeneratorDirectory()}/{$this->moduleSeederFileName}.php";
        $content = File::get($filePath);
        $contentTemplate = "\n\t\t\$this->call({$this->getSeederName()}::class);";
        if (strpos($content, $contentTemplate) === false) {
            $pattern = '/public function run\(\)(?:\s*:\s*void)?\s*\{/';
            $content = preg_replace($pattern, '$0' . $contentTemplate, $content);
            File::put($filePath, $content);
        }
    }

}
