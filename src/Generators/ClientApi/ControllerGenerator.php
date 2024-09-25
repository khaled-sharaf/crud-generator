<?php

namespace W88\CrudSystem\Generators\ClientApi;

use W88\CrudSystem\Generators\Backend\ControllerGenerator as BackendControllerGenerator;

class ControllerGenerator extends BackendControllerGenerator
{

    protected function getGeneratorDirectory(): string
    {
        return "{$this->modulePath}/app/Http/Controllers/{$this->versionNamespace}/{$this->clientDirectory}";
    }

    protected function getLocalControllerNamespace(): string
    {
        return "{$this->getControllerNamespace()}\\{$this->clientDirectory}";
    }

    protected function getUseClasses(): string
    {
        $useModel = "use {$this->getModelNamespace()}\\{$this->modelName};";
        $useRequest = "use {$this->getRequestNamespace()}\\{$this->clientDirectory}\\{$this->getRequestName()};";
        $useClasses = [
            "use {$this->getServiceNamespace()}\\{$this->clientDirectory}\\{$this->getServiceName()};",
            "use {$this->getResourceNamespace()}\\{$this->clientDirectory}\\{$this->getResourceName()};",
        ];
        if ($this->checkApiRoute('create') || $this->checkApiRoute('edit')) $useClasses[] = $useRequest;
        if ($this->checkApiRoute('edit') || $this->checkApiRoute('delete')) $useClasses[] = $useModel;
        return collect($useClasses)->implode("\n");
    }

    protected function getMethods(): string
    {
        $methods = '';
        if ($this->checkApiRoute('list', 'clientApi')) $methods .= $this->getIndexMethod();
        if ($this->checkApiRoute('create', 'clientApi')) $methods .= $this->getStoreMethod();
        if ($this->checkApiRoute('show', 'clientApi')) $methods .= $this->getShowMethod();
        if ($this->checkApiRoute('edit', 'clientApi')) $methods .= $this->getUpdateMethod();
        if ($this->checkApiRoute('delete', 'clientApi')) $methods .= $this->getDestroyMethod();
        return $methods;
    }

    protected function getIndexMethod(): string
    {
        return "\n\n\tpublic function index()\n\t{
        \${$this->modelNameCamelPlural} = \$this->{$this->getServiceNameCamel()}->list();
        {$this->getResourceName()}::collection(\${$this->modelNameCamelPlural});
        return sendData(\${$this->modelNameCamelPlural});
    }";
    }

    protected function getDestroyMethod(): string
    {
        return "\n\n\tpublic function destroy(\$id)\n\t{
        \$this->{$this->getServiceNameCamel()}->delete(\$id);
        return sendData(__('view.messages.deleted_success'));
    }";
    }

    protected function getPermissions(): string
    {
        return '';
    }

}
