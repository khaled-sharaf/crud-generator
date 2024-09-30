<?php

namespace W88\CrudSystem\Generators\Frontend;

use W88\CrudSystem\Generators\FrontendGenerator;
use Touhidurabir\StubGenerator\StubGenerator;
use Illuminate\Support\Str;

class EditGenerator extends FrontendGenerator
{

    public function generate(): void
    {
        if (!$this->checkApiRoute('edit')) return;
        $this->ensureVueStubExists('vue');
        $this->ensureVueStubExists('js');
        $this->ensureDirectoryExists();
        $this->generateFiles();
    }

    protected function getDirectoryStubName(): string
    {
        return $this->hasFormPopup() ? 'editPopup' : 'edit';
    }

    protected function getVueStubPath(): string
    {
        return __DIR__ . "/../../stubs/frontend/{$this->getDirectoryStubName()}/vue.stub";
    }

    protected function getJsStubPath(): string
    {
        return __DIR__ . "/../../stubs/frontend/{$this->getDirectoryStubName()}/js.stub";
    }

    protected function getGeneratorDirectory(): string
    {
        $dir = $this->hasFormPopup() ? 'components' : 'pages';
        return $this->getFrontendCrudPath() . "/{$dir}/{$this->getEditFileName()}";
    }

    protected function generateFiles(): void
    {
        (new StubGenerator())->from($this->getVueStubPath(), true)
            ->to($this->getGeneratorDirectory())
            ->withReplacers($this->getVueReplacers())
            ->replace(true)
            ->as($this->getEditFileName())
            ->ext('vue')
            ->save();

        (new StubGenerator())->from($this->getJsStubPath(), true)
            ->to($this->getGeneratorDirectory())
            ->withReplacers($this->getJsReplacers())
            ->replace(true)
            ->as(Str::camel($this->getEditFileName()))
            ->ext('js')
            ->save();
    }

    protected function getVueReplacers(): array
    {
        return [
            'ACTIONS' => $this->getActions(),
            'TABLE_ID' => $this->getTableId(),
            'MODEL_NAME_KEBAB' => $this->modelNameKebab,
            'COMPONENT_FORM' => $this->getFormFileName(),
            'LIST_ROUTE_NAME' => $this->getListRouteName(),
            'DIALOG_NAME' => Str::camel($this->getEditFileName()),
            'DIALOG_TITLE' => $this->getLangPath("edit_{$this->modelNameSnake}"),
            'SCRIPT' => $this->getScript(),
        ];
    }

    protected function getActions(): string
    {
        if (!$this->checkApiRoute('show') || $this->hasShowPopup()) return '';
        $hasPermission = $this->hasPermissions() ? " v-if=\"\$can('view-{$this->modelNameKebab}')\"" : '';
        return "<BtnViewTop{$hasPermission} :to=\"{ name: 'view-{$this->modelNameKebab}', params: { id: modelId } }\" />";
    }

    protected function getScript(): string
    {
        $jsFileName = Str::camel($this->getEditFileName());
        return "<script src=\"{$jsFileName}.js\"></script>";
    }

    protected function getJsReplacers(): array
    {
        return [
            'COMPONENT_FORM' => $this->getFormFileName(),
            'API_ROUTE_NAME' => $this->getApiRouteName(),
            'LIST_ROUTE_NAME' => $this->getListRouteName(),
            'DIALOG_NAME' => Str::camel($this->getEditFileName()),
        ];
    }

}
