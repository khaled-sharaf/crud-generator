<?php

namespace W88\CrudSystem\Generators\Frontend;

use W88\CrudSystem\Generators\FrontendGenerator;
use Touhidurabir\StubGenerator\StubGenerator;
use Illuminate\Support\Str;

class CreateGenerator extends FrontendGenerator
{

    public function generate(): void
    {
        $this->ensureVueStubExists('vue');
        $this->ensureVueStubExists('js');
        $this->ensureDirectoryExists();
        $this->generateFiles();
    }

    protected function getVueStubPath(): string
    {
        return __DIR__ . '/../../stubs/frontend/createAndEdit/vue.stub';
    }

    protected function getJsStubPath(): string
    {
        return __DIR__ . '/../../stubs/frontend/createAndEdit/js.stub';
    }

    protected function getGeneratorDirectory(): string
    {
        return $this->getFrontendCrudPath() . "/pages/{$this->getCreateFileName()}";
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

        (new StubGenerator())->from($this->getJsStubPath(), true)
            ->to($this->getGeneratorDirectory())
            ->withReplacers($this->getJsReplacers())
            ->replace(true)
            ->as(Str::camel($this->getCreateFileName()))
            ->ext('js')
            ->save();
    }

    protected function getVueReplacers(): array
    {
        return [
            'CLASS_PAGE' => "create-{$this->modelNameKebab}-page",
            'COMPONENT_FORM' => $this->getFormFileName(),
            'JS_FILE_NAME' => Str::camel($this->getCreateFileName()),
            'COMPONENT_FORM_PROPS' => '',
            'LOADING_COMPONENT' => '',
        ];
    }

    protected function getJsReplacers(): array
    {
        return [
            'COMPONENT_FORM' => $this->getFormFileName(),
            'DATA_FUNCTION' => '',
            'METHODS_FUNCTION' => '',
            'CREATED_FUNCTION' => '',
        ];
    }

}
