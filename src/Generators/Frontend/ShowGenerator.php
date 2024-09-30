<?php

namespace W88\CrudSystem\Generators\Frontend;

use W88\CrudSystem\Generators\FrontendGenerator;
use Touhidurabir\StubGenerator\StubGenerator;
use Illuminate\Support\Str;
use W88\CrudSystem\Facades\Field;

class ShowGenerator extends FrontendGenerator
{

    public function generate(): void
    {
        if (!$this->checkApiRoute('show')) return;
        $this->ensureVueStubExists('vue');
        $this->ensureVueStubExists('js');
        $this->ensureDirectoryExists();
        $this->generateFiles();
    }

    protected function getDirectoryStubName(): string
    {
        return $this->hasShowPopup() ? 'showPopup' : 'show';
    }

    protected function getShowFieldsStubPath(): string
    {
        return __DIR__ . "/../../stubs/frontend/showFields/vue.stub";
    }

    protected function getFieldsStubPath($fileName): string
    {
        return __DIR__ . "/../../stubs/frontend/showFields/fields/{$fileName}.stub";
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
        $dir = $this->hasShowPopup() ? 'components' : 'pages';
        return $this->getFrontendCrudPath() . "/{$dir}/{$this->getShowFileName()}";
    }

    protected function generateFiles(): void
    {
        (new StubGenerator())->from($this->getVueStubPath(), true)
            ->to($this->getGeneratorDirectory())
            ->withReplacers($this->getVueReplacers())
            ->replace(true)
            ->as($this->getShowFileName())
            ->ext('vue')
            ->save();

        (new StubGenerator())->from($this->getJsStubPath(), true)
            ->to($this->getGeneratorDirectory())
            ->withReplacers($this->getJsReplacers())
            ->replace(true)
            ->as(Str::camel($this->getShowFileName()))
            ->ext('js')
            ->save();
    }

    protected function getVueReplacers(): array
    {
        return [
            'ACTIONS' => $this->getActions(),
            'SHOW_CONTENT' => $this->getShowContent(),
            'DIALOG_NAME' => Str::camel($this->getShowFileName()),
            'DIALOG_TITLE' => $this->getLangPath("view_{$this->modelNameSnake}"),
            'SCRIPT' => $this->getScript(),
        ];
    }

    protected function getActions(): string
    {
        if (!$this->checkApiRoute('edit') || $this->hasFormPopup()) return '';
        $hasPermission = $this->hasPermissions() ? " v-if=\"\$can('edit-{$this->modelNameKebab}')\"" : '';
        return "<BtnEdit{$hasPermission} :to=\"{ name: 'edit-{$this->modelNameKebab}', params: { id: modelId } }\" />";
    }

    protected function getShowContent(): string
    {
        $content = (new StubGenerator())->from($this->getShowFieldsStubPath(), true)
            ->withReplacers([
                'FIELDS' => $this->getShowContentFields(),
            ])
            ->toString();
        return $this->hasShowPopup() ? $this->removeLeadingTab($content) : $content;
    }

    protected function getScript(): string
    {
        $jsFileName = Str::camel($this->getShowFileName());
        return "<script src=\"{$jsFileName}.js\"></script>";
    }

    protected function getJsReplacers(): array
    {
        return [
            'API_ROUTE_NAME' => $this->getApiRouteName(),
            'LIST_ROUTE_NAME' => $this->getListRouteName(),
            'DIALOG_NAME' => Str::camel($this->getShowFileName()),
        ];
    }

    protected function getShowContentFields(): string
    {
        $fields = [];
        foreach ($this->getFieldsVisibleInView() as $field) {
            $field['label'] = $this->getLangPath("table.{$field['name']}");
            $fieldReplacers = collect($field)->only('name', 'label')->mapWithKeys(fn ($value, $key) => [strtoupper($key) => $value])->toArray();
            $fields[] = (new StubGenerator())->from($this->getFieldsStubPath(Field::getStubViewFile($field)), true)
                ->withReplacers($fieldReplacers)->toString();
        }
        return implode("\n", $fields);
    }

}
