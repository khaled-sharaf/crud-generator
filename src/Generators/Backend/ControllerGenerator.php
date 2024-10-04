<?php

namespace W88\CrudSystem\Generators\Backend;

use W88\CrudSystem\Generators\BackendGenerator;
use Touhidurabir\StubGenerator\StubGenerator;
use Illuminate\Support\Str;

class ControllerGenerator extends BackendGenerator
{
    
    public function checkBeforeGenerate(): bool
    {
        return true;
    }
    
    public function generate(): void
    {
        $this->ensureStubExists();
        $this->ensureDirectoryExists();
        $this->generateController();
    }

    protected function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/backend/controller.stub';
    }

    protected function getGeneratorDirectory(): string
    {
        return "{$this->modulePath}/app/Http/Controllers/{$this->versionNamespace}";
    }

    protected function getLocalControllerNamespace(): string
    {
        return $this->getControllerNamespace();
    }

    protected function generateController(): void
    {
        (new StubGenerator)->from($this->getStubPath(), true)
            ->to($this->getGeneratorDirectory())
            ->withReplacers($this->getReplacers())
            ->replace(true)
            ->as($this->getControllerName())
            ->save();
    }

    protected function getReplacers(): array
    {
        return [
            'CLASS_NAMESPACE' => $this->getLocalControllerNamespace(),
            'CLASS_NAME' => $this->getControllerName(),
            'SERVICE_NAME' => $this->getServiceName(),
            'SERVICE_NAME_CAMEL' => $this->getServiceNameCamel(),
            'PERMISSIONS' => $this->getPermissions(),
            'METHODS' => $this->getMethods(),
            'USE_CLASSES' => $this->getUseClasses(),
        ];
    }

    protected function getUseClasses(): string
    {
        $useModel = "use {$this->getModelNamespace()}\\{$this->modelName};";
        $useRequest = "use {$this->getRequestNamespace()}\\{$this->getRequestName()};";
        $useCrudHelper = "use App\Helpers\CrudHelpers\Facades\CrudHelper;";
        $useClasses = [
            "use {$this->getServiceNamespace()}\\{$this->getServiceName()};",
            "use {$this->getResourceNamespace()}\\{$this->getResourceName()};",
        ];
        if ($this->checkApiRoute('delete') || $this->getActivationRouteOption()) $useClasses[] = $useCrudHelper;
        if ($this->checkApiRoute('edit') || $this->checkApiRoute('delete') || $this->getActivationRouteOption()) $useClasses[] = $useModel;
        if ($this->checkApiRoute('create') || $this->checkApiRoute('edit')) $useClasses[] = $useRequest;
        return collect($useClasses)->implode("\n");
    }

    protected function getMethods(): string
    {
        $methods = $this->getIndexMethod();
        if ($this->checkApiRoute('create')) $methods .= $this->getStoreMethod();
        if ($this->checkApiRoute('show')) $methods .= $this->getShowMethod();
        if ($this->checkApiRoute('edit')) $methods .= $this->getUpdateMethod();
        if ($this->checkApiRoute('delete')) $methods .= $this->getDestroyMethod();
        if ($this->getActivationRouteOption()) $methods .= $this->getActivationMethod();
        foreach ($this->getBooleanRouteFields() as $field) {
            $methods .= $this->getBooleanMethod($field);
        }
        return $methods;
    }

    protected function getIndexMethod(): string
    {
        return "\n\n\tpublic function index()\n\t{
        \${$this->modelNameCamelPlural} = \$this->{$this->getServiceNameCamel()}->tableList();
        {$this->getResourceName()}::collection(\${$this->modelNameCamelPlural});
        return sendData(\${$this->modelNameCamelPlural});
    }";
    }

    protected function getStoreMethod(): string
    {
        return "\n\n\tpublic function store({$this->getRequestName()} \$request)\n\t{
        \$data = \$request->validated();
        \$this->{$this->getServiceNameCamel()}->create(\$data);
        return sendData(__('view.messages.created_success'));
    }";
    }

    protected function getUpdateMethod(): string
    {
        return "\n\n\tpublic function update(\$id, {$this->getRequestName()} \$request)\n\t{
        \$data = \$request->validated();
        \$this->{$this->getServiceNameCamel()}->update(\$id, \$data);
        return sendData(__('view.messages.updated_success'));
    }";
    }

    protected function getShowMethod(): string
    {
        return "\n\n\tpublic function show(\$id)\n\t{
        return sendData(new {$this->getResourceName()}(\$this->{$this->getServiceNameCamel()}->show(\$id)));
    }";
    }

    protected function getDestroyMethod(): string
    {
        $hasPermission = $this->hasPermissions() ? ", '{$this->modelNameKebab}'" : '';
        return "\n\n\tpublic function destroy(\$id)\n\t{
        return sendData(CrudHelper::deleteActions(\$id, new {$this->modelName} $hasPermission));
    }";
    }

    protected function getActivationMethod(): string
    {
        $column = $this->getActivationRouteOption()['column'] ?? 'is_active';
        return "\n\n\tpublic function activation(\$id)\n\t{
        \$action = CrudHelper::toggleBoolean({$this->modelName}::findOrFail(\$id), '{$column}');
        return sendData(['changed' => \$action['isChanged']], __('view.messages.changed_success'));\n\t}";
    }

    protected function getBooleanMethod(array $field): string
    {
        $method = Str::camel($field['route']);
        return "\n\n\tpublic function {$method}(\$id)\n\t{
        \$action = CrudHelper::toggleBoolean({$this->modelName}::findOrFail(\$id), '{$field['name']}');
        return sendData(['changed' => \$action['isChanged']], __('view.messages.changed_success'));\n\t}";
    }

    protected function getPermissions(): string
    {
        if (!$this->hasPermissions()) return '';
        $permissions = "\$this->middleware('can:view-list-{$this->modelNameKebab}')->only('index');";
        if ($this->checkApiRoute('show')) $permissions .= "\n\t\t\$this->middleware('can:view-{$this->modelNameKebab}')->only('show');";
        if ($this->checkApiRoute('create')) $permissions .= "\n\t\t\$this->middleware('can:create-{$this->modelNameKebab}')->only('store');";
        if ($this->checkApiRoute('edit')) $permissions .= "\n\t\t\$this->middleware('can:edit-{$this->modelNameKebab}')->only('update');";
        return $permissions . "\n\t\t";
    }

}
