<?php

namespace W88\CrudSystem\Generators\Backend;

use W88\CrudSystem\Generators\BackendGenerator;
use Touhidurabir\StubGenerator\StubGenerator;

class PermissionGenerator extends BackendGenerator
{

    public function checkBeforeGenerate(): bool
    {
        return $this->hasPermissions();
    }
    
    public function generate(): void
    {
        $this->ensureStubExists();
        $this->ensureDirectoryExists();
        $this->generatePermission();
    }

    protected function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/backend/permission.stub';
    }

    protected function getGeneratorDirectory(): string
    {
        return "{$this->modulePath}/config/permissions";
    }

    protected function generatePermission(): void
    {
        (new StubGenerator)->from($this->getStubPath(), true)
            ->to($this->getGeneratorDirectory())
            ->withReplacers($this->getReplacers())
            ->replace(true)
            ->as($this->modelNameSnake)
            ->save();
    }

    protected function getReplacers(): array
    {
        return [
            'MODULE_NAME_SNAKE' => $this->moduleNameSnake,
            'PERMISSIONS' => $this->getPermissionsTemplate(),
        ];
    }

    protected function getPermissionsTemplate(): string
    {
        return collect($this->getPermissionsTranslated())->map(function ($permission, $name) {
            return "\n\t\t'{$name}',";
        })->join('');
    }

}
