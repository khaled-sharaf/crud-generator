<?php

namespace W88\CrudSystem\Generators\Frontend;

use W88\CrudSystem\Generators\FrontendGenerator;
use Touhidurabir\StubGenerator\StubGenerator;
use Illuminate\Support\Str;
use W88\CrudSystem\Facades\Field;

class FormGenerator extends FrontendGenerator
{

    public function generate(): void
    {
        if (!$this->checkApiRoute('create') && !$this->checkApiRoute('edit')) return;
        $this->ensureVueStubExists('vue');
        $this->ensureVueStubExists('js');
        $this->ensureDirectoryExists();
        $this->generateFiles();
    }

    protected function getFieldsStubPath($fileName): string
    {
        return __DIR__ . "/../../stubs/frontend/form/fields/{$fileName}.stub";
    }

    protected function getVueStubPath(): string
    {
        return __DIR__ . "/../../stubs/frontend/form/vue.stub";
    }

    protected function getJsStubPath(): string
    {
        return __DIR__ . "/../../stubs/frontend/form/js.stub";
    }

    protected function getGeneratorDirectory(): string
    {
        return "{$this->getFrontendCrudPath()}/components/{$this->getFormFileName()}";
    }

    protected function generateFiles(): void
    {
        (new StubGenerator())->from($this->getVueStubPath(), true)
            ->to($this->getGeneratorDirectory())
            ->withReplacers($this->getVueReplacers())
            ->replace(true)
            ->as($this->getFormFileName())
            ->ext('vue')
            ->save();

        (new StubGenerator())->from($this->getJsStubPath(), true)
            ->to($this->getGeneratorDirectory())
            ->withReplacers($this->getJsReplacers())
            ->replace(true)
            ->as(Str::camel($this->getFormFileName()))
            ->ext('js')
            ->save();
    }

    protected function getVueReplacers(): array
    {
        return [
            'FIELDS' => $this->getVueFormFields(),
            'FORM_NAME' => Str::camel($this->getFormFileName()),
            'SCRIPT' => $this->getScript(),
        ];
    }

    protected function getScript(): string
    {
        $jsFileName = Str::camel($this->getFormFileName());
        return "<script src=\"./{$jsFileName}.js\"></script>";
    }

    protected function getJsReplacers(): array
    {
        return [
            'FIELDS' => $this->getJsFormFields(),
            'DECLARED_LOOKUPS' => $this->getDeclaredLookups(),
            'FORMDATA_TYPE' => count($this->getFileFields()) ? 'Form' : '',
            'API_ROUTE_NAME' => $this->getApiRouteName(),
            'VALIDATION_FIELDS' => $this->getJsFormFieldsValidation(),
            'TRANSLATION_FIELDS' => $this->getJsFormFieldsTranslation(),
        ];
    }

    protected function getVueFormFields(): string
    {
        $fields = [];
        $tabs = "\n\t\t\t\t\t\t";
        foreach ($this->getFieldsVisibleInForm() as $field) {
            $stubPath = $this->getFieldsStubPath(Field::getStubFormFile($field));
            $field['label'] = $this->getLangPath("table.{$field['name']}");
            $fieldReplacers = collect($field)->only('name', 'label')->mapWithKeys(fn ($value, $key) => [strtoupper($key) => $value])->toArray();
            $fieldReplacers = array_merge($fieldReplacers, [
                'FORM_NAME_ATTR' => "{$tabs}formName=\"" . Str::camel($this->getFormFileName()) . '"',
                'TYPE' => 'text',
                'REQUIRED' => '',
                'TRANSLATABLE' => '',
            ]);
            if (Field::hasFile($field)) {
                $fieldReplacers['TYPE'] = str_replace('multi_', '', $field['type']);
            }
            if ($field['type'] == 'textarea' || $field['type'] == 'number') {
                $fieldReplacers['TYPE'] = $field['type'];
            }
            if (!Field::isNullable($field)) {
                $fieldReplacers['REQUIRED'] = "{$tabs}star-required";
            }
            if (Field::isTranslatable($field)) {
                $fieldReplacers['TRANSLATABLE'] = "{$tabs}translatable";
            }
            $fields[] = (new StubGenerator())->from($stubPath, true)->withReplacers($fieldReplacers)->toString();
        }
        return implode("\n", $fields);
    }

    protected function getJsFormFields(): string
    {
        return collect($this->getFieldsVisibleInForm())->map(function ($field) {
            $default = Field::hasDefault($field) ? json_encode($field['default']) : 'null';
            $value = Field::isTranslatable($field) ? '{}' : (Field::isFrontArray($field) ? '[]' : $default);
            return "\n\t\t\t\t{$field['name']}: {$value}";
        })->implode(',');
    }

    protected function getDeclaredLookups(): string
    {
        return '';
    }

    protected function getJsFormFieldsValidation(): string
    {
        return collect($this->getFieldsVisibleInForm())->map(function ($field) {
            if (Field::isNullable($field) || !Field::hasValidation($field)) return '';
            return "\n\t\t\t\t{$field['name']}: 'required'";
        })->filter(fn ($rule) => !empty($rule))->implode(',');
    }

    protected function getJsFormFieldsTranslation(): string
    {
        return collect($this->getTranslatableFields())->map(function ($field) {
            return "\n\t\t\tthis.modelEdit.{$field['name']} = this.modelEdit.{$field['name']}_trans";
        })->implode('');
    }

}
