<?php

namespace W88\CrudSystem\Generators\Frontend;

use W88\CrudSystem\Generators\FrontendGenerator;
use Touhidurabir\StubGenerator\StubGenerator;
use Illuminate\Support\Str;

class EditGenerator extends FrontendGenerator
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
        return $this->getFrontendCrudPath() . "/pages/{$this->getEditFileName()}";
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
            'CLASS_PAGE' => "edit-{$this->modelNameKebab}-page",
            'COMPONENT_FORM' => $this->getFormFileName(),
            'JS_FILE_NAME' => Str::camel($this->getEditFileName()),
            'COMPONENT_FORM_PROPS' => "v-if=\"model\" :modelEdit=\"model\" formType=\"edit\" ",
            'LOADING_COMPONENT' => "\n\t\t\t<q-card v-else>
                <q-card-section style=\"min-height: 400px\">
                    <q-inner-loading  showing color=\"primary\" />
                </q-card-section>
            </q-card>",
        ];
    }

    protected function getJsReplacers(): array
    {
        return [
            'COMPONENT_FORM' => $this->getFormFileName(),
            'DATA_FUNCTION' => $this->getJsDataFunction(),
            'METHODS_FUNCTION' => $this->getJsMethodsFunction(),
            'CREATED_FUNCTION' => $this->getJsCreatedFunction(),
        ];
    }

    protected function getJsDataFunction(): string
    {
        return "\n\tdata() {
        return {
            model: null,
            modelId: null,
        }
    },";
    }

    protected function getJsMethodsFunction(): string
    {
        return "\n\tmethods: {
        getModel() {
            this.modelId = this.\$route.params.id
            this.\$request('get').url(`{$this->getApiRouteName()}/\${this.modelId}`).send().then(res => {
                this.model = res.data
            }).catch(err => {
                if (err.statusCode == 404) {
                    this.\$router.push({name: '{$this->getListRouteName()}'})
                }
            })
        }
    },";
    }

    protected function getJsCreatedFunction(): string
    {
        return "\n\tcreated() {
        this.getModel()
    },";
    }

}
