<?php

namespace W88\CrudSystem\Generators\Backend;

use W88\CrudSystem\Generators\Generator;
use Illuminate\Support\Facades\File;
use Touhidurabir\StubGenerator\Facades\StubGenerator;

class ControllerGenerator extends Generator
{

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

    protected function ensureStubExists(): void
    {
        $stubPath = $this->getStubPath();
        if (!File::exists($stubPath)) {
            throw new \Exception("Stub file not found at path: {$stubPath}");
        }
    }

    protected function ensureDirectoryExists(): void
    {
        $directory = $this->getControllerDirectory();
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    protected function generateController(): void
    {
        StubGenerator::from($this->getStubPath(), true)
            ->to($this->getControllerDirectory())
            ->withReplacers($this->getReplacers())
            ->replace(true)
            ->as($this->getControllerName())
            ->save();
    }

    protected function getReplacers(): array
    {
        return [
            'CLASS_NAMESPACE' => $this->getControllerNamespace(),
            'CLASS_NAME' => $this->getControllerName(),
            'MODEL' => $this->modelName,
            'MODEL_LOWER' => $this->modelNameCamel,
            'MODEL_NAMESPACE' => $this->modelNamespace(),
            'SERVICE_NAMESPACE' => $this->getServiceNamespace(),
            'SERVICE_NAME' => $this->getServiceName(),
            'SERVICE_NAME_CAMEL' => $this->getServiceNameCamel(),
            'REQUEST_NAME' => $this->getRequestName(),
            'REQUEST_NAMESPACE' => $this->getRequestNamespace(),
            'RESOURCE_NAME' => $this->getResourceName(),
            'RESOURCE_NAMESPACE' => $this->getResourceNamespace(),
            'PERMISSIONS' => $this->getPermissions(),
            'METHODS' => $this->getMethods(),
        ];
    }

    protected function getMethods(): string
    {
        $methods = $this->getIndexMethod();
        if ($this->hasCreateRoute()) $methods .= $this->getStoreMethod();
        if ($this->hasProfileRoute()) $methods .= $this->getShowMethod();
        if ($this->hasUpdateRoute()) $methods .= $this->getUpdateMethod();
        if ($this->hasDeleteRoute()) $methods .= $this->getDestroyMethod();
        if ($this->hasActivationRoute()) $methods .= $this->getActivationMethod();
        return $methods;
    }

    protected function getIndexMethod(): string
    {
        return 'public function index()
    {
        $' . $this->modelNameCamelPlural . ' = $this->' . $this->getServiceNameCamel() . '->tableList();
        ' . $this->getResourceName() . '::collection($' . $this->modelNameCamelPlural . ');
        return sendData($' . $this->modelNameCamelPlural . ');
    }';
    }

    protected function getStoreMethod(): string
    {
        return "\n\n\t" . 'public function store(' . $this->getRequestName() . ' $request)
    {
        $data = $request->validated();
        $this->' . $this->getServiceNameCamel() . '->create($data);
        return sendData(__(\'view.messages.created_success\'));
    }';
    }

    protected function getUpdateMethod(): string
    {
        return "\n\n\t" . 'public function update($id, ' . $this->getRequestName() . ' $request)
    {
        $' . $this->modelNameCamel . ' = ' . $this->modelName . '::findOrFail($id);
        $data = $request->validated();
        $this->' . $this->getServiceNameCamel() . '->update($' . $this->modelNameCamel . ', $data);
        return sendData(__(\'view.messages.updated_success\'));
    }';
    }

    protected function getShowMethod(): string
    {
        return "\n\n\t" . 'public function show($id)
    {
        return sendData(new ' . $this->getResourceName() . '($this->' . $this->getServiceNameCamel() . '->show($id)));
    }';
    }

    protected function getDestroyMethod(): string
    {
        $hasPermission = $this->hasPermissions() ? ', \'' . $this->modelNameKebab . '\'' : '';
        return "\n\n\t" . 'public function destroy($id)
    {
        return sendData(CrudHelper::deleteActions($id, new ' . $this->modelName . $hasPermission . '));
    }';
    }

    protected function getActivationMethod(): string
    {
        return "\n\n\t" . 'public function activation($id)
    {
        $action = CrudHelper::toggleBoolean(' . $this->modelName . '::findOrFail($id), \'is_active\');
        return sendData([\'changed\' => $action[\'isChanged\']], __(\'' . $this->moduleNameSnake . '::view.' . $this->modelNameSnake . '_crud.messages.\' . ($action[\'model\']->is_active ? \'activated\' : \'deactivated\')));
    }';
    }

    protected function getPermissions(): string
    {
        if (!$this->hasPermissions()) return '';
        $permissions = '$this->middleware(\'can:view-list-' . $this->modelNameKebab . '\')->only(\'index\');';
        if ($this->hasProfileRoute()) $permissions .= "\n\t\t" . '$this->middleware(\'can:view-profile-' . $this->modelNameKebab . '\')->only(\'show\');';
        if ($this->hasCreateRoute()) $permissions .= "\n\t\t" . '$this->middleware(\'can:create-' . $this->modelNameKebab . '\')->only(\'store\');';
        if ($this->hasUpdateRoute()) $permissions .= "\n\t\t" . '$this->middleware(\'can:edit-' . $this->modelNameKebab . '\')->only(\'update\');';
        return $permissions . "\n\t\t";
    }
}
