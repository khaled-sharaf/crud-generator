<?php

namespace Khaled\CrudSystem\Generators\ClientApi;

use Khaled\CrudSystem\Generators\Backend\ResourceGenerator as BackendResourceGenerator;

class ResourceGenerator extends BackendResourceGenerator
{

    public function checkBeforeGenerate(): bool
    {
        return $this->checkApiRoute('list', 'clientApi') || $this->checkApiRoute('show', 'clientApi');
    }
    
    protected function getGeneratorDirectory(): string
    {
        return "{$this->modulePath}/app/Resources/{$this->versionNamespace}/{$this->clientDirectory}";
    }

    protected function getLocalResourceNamespace(): string
    {
        return "{$this->getResourceNamespace()}\\{$this->clientDirectory}";
    }

    protected function getTimestampsFields(): string
    {
        return '';
    }

}
