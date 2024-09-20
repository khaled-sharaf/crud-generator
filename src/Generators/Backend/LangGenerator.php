<?php

namespace W88\CrudSystem\Generators\Backend;

use W88\CrudSystem\Generators\Generator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Touhidurabir\StubGenerator\Facades\StubGenerator;

class LangGenerator extends Generator
{

    public function generate(): void
    {
        $this->ensureStubExists();
        $this->ensureFileExists();
        $this->insertLang();
    }

    protected function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/backend/lang.stub';
    }

    protected function getLangDirectory(): string
    {
        return $this->modulePath . '/lang/en';
    }

    protected function getFilePath(): string
    {
        return $this->getLangDirectory() . '/view.php';
    }

    protected function ensureStubExists(): void
    {
        $stubPath = $this->getStubPath();
        if (!File::exists($stubPath)) {
            throw new \Exception("Stub file not found at path: {$stubPath}");
        }
    }

    protected function ensureFileExists(): void
    {
        $filePath = $this->getFilePath();
        if (!File::exists($filePath)) {
            StubGenerator::from($this->getStubPath(), true)->to($this->getLangDirectory())->as('view')->save();
        }
    }

    protected function getContentFile(): string
    {
        return File::get($this->getFilePath());
    }

    protected function insertLang(): void
    {
        $filePath = $this->getFilePath();
        $contentFile = $this->getContentFile();
        $contentTemplate = $this->getContentTemplate();
        if (strpos($contentFile, $contentTemplate) === false) {
            $pattern = '/\s*\];/';
            $comma = ',';
            if (preg_match('/,\s*\];/', $contentFile)) $pattern = '/,\s*\];/';
            if (preg_match('/return \[\s*\];/', $contentFile)) $comma = '';
            $contentFile = preg_replace($pattern, $comma . "\n\n\t". $contentTemplate . "\n];", $contentFile);
            File::put($filePath, $contentFile);
        }
    }

    protected function getContentTemplate(): string
    {
        $modelTitle = Str::title($this->modelNameSnakePlural);
        $content = "'{$this->modelNameSnake}_crud' => [\n\t\t'label' => '{$modelTitle}',";
        $content .= $this->getPermissionsTemplate();
        $content .= $this->getValidationTemplate();
        $content .= $this->getMessagesTemplate();
        return $content . "\n\t],\n";
    }

    protected function getPermissionsTemplate(): string
    {
        if (!$this->hasPermissions()) return '';
        $permissions = $this->getPermissionsTranslated();
        $permissionsResult = "\n\t\t'permissions' => [";
        $permissionsResult .= collect($permissions)->map(function ($permission, $name) {
            return "\n\t\t\t'{$name}' => '{$permission}',";
        })->join('');
        return $permissionsResult . "\n\t\t],";
    }

    protected function getValidationTemplate(): string
    {
        $validation = "\n\t\t'validation' => [";
        $validation .= collect($this->getFields())->map(function ($field, $name) {
            return "\n\t\t\t'{$name}' => '{$field['label']}',";
        })->join('');
        return $validation . "\n\t\t],";
    }

    protected function getMessagesTemplate(): string
    {
        $modelTitle = strtolower(Str::title($this->modelNameSnake));
        $messages = "\n\t\t'messages' => [";
        if ($this->getActivationRouteOption()) {
            $messages .= "\n\t\t\t'activated' => 'The {$modelTitle} has been activated successfully',";
            $messages .= "\n\t\t\t'deactivated' => 'The {$modelTitle} has been deactivated successfully',";
        }
        return $messages . "\n\t\t],";
    }
}
