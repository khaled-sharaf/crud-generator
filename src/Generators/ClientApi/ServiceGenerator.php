<?php

namespace W88\CrudSystem\Generators\ClientApi;

use W88\CrudSystem\Generators\Backend\ServiceGenerator as BackendServiceGenerator;

class ServiceGenerator extends BackendServiceGenerator
{

    public function checkBeforeGenerate(): bool
    {
        return $this->hasClientApi();
    }
    
    protected function getGeneratorDirectory(): string
    {
        return "{$this->modulePath}/app/Services/{$this->clientDirectory}";
    }

    protected function getLocalServiceNamespace(): string
    {
        return "{$this->getServiceNamespace()}\\{$this->clientDirectory}";
    }

    protected function getUseClasses(): string
    {
        $useClasses = ["use {$this->getModelNamespace()}\\{$this->modelName};"];
        if ($this->checkApiRoute('list', 'clientApi')) $useClasses[] = 'use App\Helpers\CrudHelpers\Facades\CrudHelper;';
        return collect($useClasses)->implode("\n") . "\n";
    }

    protected function getMethods(): string
    {
        $methods = '';
        if ($this->checkApiRoute('list', 'clientApi')) $methods .= $this->getIndexMethod();
        if ($this->checkApiRoute('show', 'clientApi')) $methods .= $this->getShowMethod();
        if ($this->checkApiRoute('create', 'clientApi')) $methods .= $this->getStoreMethod();
        if ($this->checkApiRoute('edit', 'clientApi')) $methods .= $this->getUpdateMethod();
        if ($this->checkApiRoute('delete', 'clientApi')) $methods .= $this->getDeleteMethod();
        return $methods;
    }

    protected function getIndexMethod(): string
    {
        return "\n\n\tpublic function list()\n\t{
        \${$this->modelNameCamelPlural} = {$this->modelName}::query();
        \$filters = [];
        \$paginated = true;
        return CrudHelper::tableList(\${$this->modelNameCamelPlural}, \$filters, \$paginated);
    }";
    }

    protected function getDeleteMethod(): string
    {
        return "\n\n\tpublic function delete(\$id)\n\t{
        \${$this->modelNameCamel} = {$this->modelName}::findOrFail(\$id);
        \${$this->modelNameCamel}->delete();
    }";
    }

}
