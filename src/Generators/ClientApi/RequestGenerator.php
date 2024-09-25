<?php

namespace W88\CrudSystem\Generators\ClientApi;

use W88\CrudSystem\Generators\Backend\RequestGenerator as BackendRequestGenerator;

class RequestGenerator extends BackendRequestGenerator
{
 
    protected function conditionForCreate(): bool
    {
        return $this->checkApiRoute('create', 'clientApi') || $this->checkApiRoute('edit', 'clientApi');
    }

    protected function getGeneratorDirectory(): string
    {
        return "{$this->modulePath}/app/Http/Requests/{$this->versionNamespace}/{$this->clientDirectory}";
    }

    protected function getLocalRequestNamespace(): string
    {
        return "{$this->getRequestNamespace()}\\{$this->clientDirectory}";
    }
    
}
