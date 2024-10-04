<?php

namespace W88\CrudSystem\Generators\Frontend;

use W88\CrudSystem\Generators\FrontendGenerator;
use Touhidurabir\StubGenerator\StubGenerator;
use Illuminate\Support\Str;

class CreateGenerator extends FrontendGenerator
{

    public function checkBeforeGenerate(): bool
    {
        return $this->checkApiRoute('create');
    }
    
    public function generate(): void
    {
        $this->ensureVueStubExists('vue');
        $this->ensureVueStubExists('js');
        $this->ensureDirectoryExists();
        $this->generateFiles();
    }

    protected function getDirectoryStubName(): string
    {
        return $this->hasFormPopup() ? 'createPopup' : 'create';
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
        return $this->getFrontendCrudPath() . "/{$dir}/{$this->getCreateFileName()}";
    }

    protected function generateFiles(): void
    {
        (new StubGenerator())->from($this->getVueStubPath(), true)
            ->to($this->getGeneratorDirectory())
            ->withReplacers($this->getVueReplacers())
            ->replace(true)
            ->as($this->getCreateFileName())
            ->ext('vue')
            ->save();
    }

    protected function getVueReplacers(): array
    {
        return [
            'MODEL_NAME_KEBAB' => $this->modelNameKebab,
            'TABLE_ID' => $this->getTableId(),
            'COMPONENT_FORM' => $this->getFormFileName(),
            'LIST_ROUTE_NAME' => $this->getListRouteName(),
            'POPUP_WIDTH' => $this->getFormPopupWidth(),
            'DIALOG_NAME' => Str::camel($this->getCreateFileName()),
            'DIALOG_TITLE' => $this->getLangPath("create_{$this->modelNameSnake}"),
            'SCRIPT' => $this->getScript(),
        ];
    }

    protected function getJsReplacers(): array
    {
        return [
            'COMPONENT_FORM' => $this->getFormFileName(),
        ];
    }

    protected function getScript(): string
    {
        return "<script>\n" . (new StubGenerator())->from($this->getJsStubPath(), true)
            ->withReplacers($this->getJsReplacers())
            ->toString() . "\n</script>";
    }

}
