<?php

namespace Khaled\CrudSystem\Generators\ClientApi;

use Khaled\CrudSystem\Generators\Backend\RequestGenerator as BackendRequestGenerator;

class RequestGenerator extends BackendRequestGenerator
{
 
    public function checkBeforeGenerate(): bool
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
