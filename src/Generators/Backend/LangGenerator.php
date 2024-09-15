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
        $content = "'{$this->modelNameSnake}_crud' => [
        'label' => '{$modelTitle}',";
        $content .= $this->getPermissionsTemplate();
        $content .= $this->getValidationTemplate();
        $content .= $this->getMessagesTemplate();
        return $content . "\n\t],\n";
    }

    protected function getPermissionsTemplate(): string
    {
        if (!$this->hasPermissions()) return '';
        $modelTitle = Str::title($this->modelNameKebab);
        $permissions = "\n\t\t'permissions' => [\n\t\t\t'view-list-{$this->modelNameKebab}' => 'View {$modelTitle} List',";
        if ($this->hasTableExport()) $permissions .= "\n\t\t\t'export-list-{$this->modelNameSnake}' => 'Export {$modelTitle} List',";
        if ($this->hasProfileRoute()) $permissions .= "\n\t\t\t'view-profile-{$this->modelNameSnake}' => 'View {$modelTitle} Profile',";
        if ($this->hasCreateRoute()) $permissions .= "\n\t\t\t'create-{$this->modelNameSnake}' => 'Create {$modelTitle}',";
        if ($this->hasUpdateRoute()) $permissions .= "\n\t\t\t'edit-{$this->modelNameSnake}' => 'Edit {$modelTitle}',";
        if ($this->hasDeleteRoute()) $permissions .= "\n\t\t\t'delete-{$this->modelNameSnake}' => 'Delete {$modelTitle}',";
        if ($this->hasSoftDeletes()) $permissions .= "\n\t\t\t'force-delete-{$this->modelNameSnake}' => 'Delete Forever {$modelTitle}',";
        if ($this->hasSoftDeletes()) $permissions .= "\n\t\t\t'restore-{$this->modelNameSnake}' => 'Restore {$modelTitle}',";
        if ($this->hasSoftDeletes()) $permissions .= "\n\t\t\t'view-trashed-{$this->modelNameSnake}-list' => 'View Trashed {$modelTitle} List',";
        if ($this->hasActivationRoute()) $permissions .= "\n\t\t\t'activation-{$this->modelNameSnake}' => 'Activation {$modelTitle}',";
        return $permissions . "\n\t\t],";
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
        if ($this->hasActivationRoute()) $messages .= "\n\t\t\t'activated' => 'The {$modelTitle} has been activated successfully',";
        if ($this->hasActivationRoute()) $messages .= "\n\t\t\t'deactivated' => 'The {$modelTitle} has been deactivated successfully',";
        return $messages . "\n\t\t],";
    }
}
