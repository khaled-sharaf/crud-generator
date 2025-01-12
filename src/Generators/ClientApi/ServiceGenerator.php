<?php

namespace Khaled\CrudSystem\Generators\ClientApi;

use Khaled\CrudSystem\Generators\Backend\ServiceGenerator as BackendServiceGenerator;
use Khaled\CrudSystem\Facades\Field;

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
        if ($this->checkApiRoute('show', 'clientApi') || $this->checkApiRoute('edit', 'clientApi') || $this->checkApiRoute('delete', 'clientApi')) $methods .= $this->getShowMethod();
        if ($this->checkApiRoute('create', 'clientApi')) $methods .= $this->getStoreMethod();
        if ($this->checkApiRoute('edit', 'clientApi')) $methods .= $this->getUpdateMethod();
        if ($this->checkApiRoute('delete', 'clientApi')) $methods .= $this->getDestroyMethod();
        return $methods;
    }

    protected function getIndexMethod(): string
    {
        $with = $this->getWith($this->getFieldsHasRelationForList(), 'before');
        return "\n\n\tpublic function list()\n\t{
        \$query = {$this->modelName}::query(){$with};
        \$filters = [];
        \$paginated = true;
        return CrudHelper::tableList(\$query, \$filters, \$paginated);
    }";
    }

    protected function getDestroyMethod(): string
    {
        return "\n\n\tpublic function delete(\$id)\n\t{
        \${$this->modelNameCamel} = \$this->show(\$id);
        \${$this->modelNameCamel}->delete();
    }";
    }

    protected function getFieldsHasRelationForList(): array
    {
        return collect($this->getFields())->filter(fn ($field) => Field::hasRelation($field))->toArray();
    }

    protected function getFieldsHasRelationForShow(): array
    {
        return collect($this->getFields())->filter(fn ($field) => Field::hasRelation($field))->toArray();
    }

}
