<?php

namespace W88\CrudSystem\Generators\Backend;

use W88\CrudSystem\Generators\Generator;
use Illuminate\Support\Facades\File;
use Touhidurabir\StubGenerator\Facades\StubGenerator;
use Illuminate\Support\Str;

class ModelGenerator extends Generator
{

    public function generate(): void
    {
        $this->ensureStubExists();
        $this->ensureDirectoryExists();
        $this->generateModel();
    }

    protected function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/backend/model.stub';
    }

    protected function ensureStubExists(): void
    {
        $stubPath = $this->getStubPath();
        if (!File::exists($stubPath)) {
            throw new \Exception("Stub file not found at path: {$stubPath}");
        }
    }

    protected function getModelDirectory(): string
    {
        return $this->modulePath . '/app/Models/';
    }

    protected function ensureDirectoryExists(): void
    {
        $directory = $this->getModelDirectory();
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    protected function generateModel(): void
    {
        StubGenerator::from($this->getStubPath(), true)
            ->to($this->getModelDirectory(), true, true)
            ->withReplacers($this->getReplacers())
            ->as($this->modelName)
            ->replace(true)
            ->save();
    }

    protected function getReplacers(): array
    {
        return [
            'CLASS_NAMESPACE' => $this->moduleNamespace . '\app\Models',
            'CLASS_NAME' => $this->modelName,
            'USE_CLASSES' => $this->getUseClassesString(),
            'USES' => $this->getUses(),
            'FILLABLE' => $this->getFillable(),
            'OPTIONS' => $this->getOptions(),
            'ATTRS' => $this->getAttrs(),
            'RELATIONS' => $this->getRelations(),
            'SCOPES' => $this->getScopes(),
        ];
    }

    protected function getUseClassesString(): string
    {
        $useClasses = $this->getUseClasses();
        return count($useClasses) ? collect($useClasses)->implode(";\n") . ';' : '';
    }

    protected function getUseClasses(): array
    {
        $useSoftDeletes = 'use Illuminate\Database\Eloquent\SoftDeletes';
        $useActivityLogHelper = 'use App\Helpers\CrudHelpers\Traits\ActivityLogHelper';
        $useFileHelper = 'use App\Helpers\File\Traits\FileHelper';
        $useClasses = [];
        if ($this->hasSoftDeletes()) $useClasses[] = $useSoftDeletes;
        if ($this->hasAddLogs()) $useClasses[] = $useActivityLogHelper;
        return $useClasses;
    }

    protected function getUses(): string
    {
        $uses = collect($this->getUseClasses())->map(fn ($use) => Str::of($use)->explode('\\')->last() )->implode(", ");
        return $uses ? 'use ' . $uses . ";\n" : '';
    }

    protected function getFillable(): string
    {
        return collect($this->getFields())->map(function ($field, $name) {
            return "\n\t\t'$name'";
        })->implode(",");
    }

    protected function getOptions(): string
    {
        // protected static function booted()
        // {
        //     static::creating(function ($model) {
        //     });
        // }
        return '';
    }

    protected function getAttrs(): string
    {
        return '';
    }

    protected function getRelations(): string
    {
        return '';
    }

    protected function getScopes(): string
    {
        return '';
    }

}
