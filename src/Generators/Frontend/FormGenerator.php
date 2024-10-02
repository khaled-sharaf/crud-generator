<?php

namespace W88\CrudSystem\Generators\Frontend;

use W88\CrudSystem\Generators\FrontendGenerator;
use Touhidurabir\StubGenerator\StubGenerator;
use Illuminate\Support\Str;
use W88\CrudSystem\Facades\Field;

class FormGenerator extends FrontendGenerator
{

    public function checkBeforeGenerate(): bool
    {
        return $this->checkApiRoute('create') || $this->checkApiRoute('edit');
    }
    
    public function generate(): void
    {
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
            'FORMDATA_TYPE' => count($this->getFileFields()) ? 'Form' : '',
            'API_ROUTE_NAME' => $this->getApiRouteName(),
            'VALIDATION_FIELDS' => $this->getJsFormFieldsValidation(),
            'DECLARED_LOOKUPS' => $this->getJsDeclaredLookups(),
            'GET_LOOKUPS' => $this->getJsGetLookups(),
            'TRANSLATION_FIELDS_IN_EDIT' => $this->getJsFormFieldsTranslationInEdit(),
            'TRANSLATION_FIELDS_IN_CREATE' => $this->getJsFormFieldsTranslationInCreate(),
        ];
    }

    protected function getJsDeclaredLookups(): string
    {
        return collect($this->getFieldsHasBackendLookupOnly())->map(function ($field) {
            $lookupName = Str::camel($this->getLookupName($field['name']));
            return "\n\t\t\t{$lookupName}: []";
        })->implode(",");
    }

    protected function getJsGetLookups(): string
    {
        return collect($this->getFieldsHasBackendLookupOnly())->map(function ($field) {
            $lookupName = Str::camel($this->getLookupName($field['name']));
            return "\n\t\tthis.{$lookupName} = await this.\$getLookup('{$this->getLookupApiRouteName($field['name'])}')";
        })->implode(",");
    }

    protected function getVueFormFields(): string
    {
        $fields = [];
        foreach ($this->getFieldsVisibleInForm() as $field) {
            $stubPath = $this->getFieldsStubPath(Field::getStubFormFile($field));
            $field['label'] = $this->getLangPath("table.{$field['name']}");
            $fieldReplacers = collect($field)->only('name', 'label')->mapWithKeys(fn ($value, $key) => [strtoupper($key) => $value])->toArray();
            $fieldReplacers = array_merge($fieldReplacers, [
                'FORM_NAME_ATTR' => $this->getFormNameAttr(),
                'TYPE' => $this->getFormFieldType($field),
                'REQUIRED' => $this->getRequiredAttr($field),
                'REQUIRED_HTML' => $this->getRequiredHtml($field),
                'TRANSLATABLE' => $this->getTranslatableAttr($field),
                'TITLE_TRUE' => $this->getTitleTrue($field),
                'TITLE_FALSE' => $this->getTitleFalse($field),
                'FILE_TYPE' => $this->getFileType($field),
                'MULTI_FILE_TYPE' => $this->getMultiFileType($field),
                'FILE_ICON' => $this->getFileIcon($field),
                'OPTIONS' => $this->getLookupNameForOptionGroup($field),
                'OPTIONS_GROUP_TYPE' => $this->getOptionsGroupType($field),
                'IS_MULTI_SELECT' => $this->getIsMultiSelect($field),
                'IS_USE_CHIPS' => $this->getIsUseChips($field),
            ]);
            $fields[] = (new StubGenerator())->from($stubPath, true)->withReplacers($fieldReplacers)->toString();
        }
        return implode("\n", $fields);
    }

    protected function getFormNameAttr($tabs = "\n\t\t\t\t\t\t"): string
    {
        return "{$tabs}formName=\"" . Str::camel($this->getFormFileName()) . '"';
    }

    protected function getFormFieldType(array $field): string
    {
        return $field['type'] == 'textarea' || $field['type'] == 'number' ? $field['type'] : 'text';
    }

    protected function getRequiredAttr(array $field, $tabs = "\n\t\t\t\t\t\t"): string
    {
        return !Field::isNullable($field) ? "{$tabs}star-required" : '';
    }

    protected function getRequiredHtml(array $field): string
    {
        return !Field::isNullable($field) ? '<span class="req-star"></span>' : '';
    }
    
    protected function getTranslatableAttr(array $field, $tabs = "\n\t\t\t\t\t\t"): string
    {
        return Field::isTranslatable($field) ? "{$tabs}translatable" : '';
    }

    protected function getFileType(array $field): string
    {
        return Field::hasFileImage($field) ? 'image' : (Field::hasFileVideo($field) ? 'video' : 'any');
    }
    
    protected function getMultiFileType(array $field, $tabs = "\n\t\t\t\t\t\t"): string
    {
        return Field::hasFileImage($field) ? "{$tabs}onlyImage" : (Field::hasFileVideo($field) ? "{$tabs}onlyVideo" : '');
    }

    protected function getFileIcon(array $field): string
    {
        return Field::hasFileImage($field) ? 'add_photo_alternate' : (Field::hasFileVideo($field) ? 'cloud_upload' : 'file_upload');
    }

    protected function getLookupNameForOptionGroup(array $field): string
    {
        $lookupName = $this->getLookupName($field['name']);
        return Field::hasLookupFrontend($field) ? $lookupName : Str::camel($lookupName);
    }

    protected function getOptionsGroupType(array $field): string
    {
        return $field['type'] == 'multi_checkbox' ? 'checkbox' : 'radio';
    }

    protected function getIsMultiSelect(array $field, $tabs = "\n\t\t\t\t\t\t\t"): string
    {
        return $field['type'] == 'multi_select' ? "{$tabs}multiple" : '';
    }

    protected function getIsUseChips(array $field, $tabs = "\n\t\t\t\t\t\t\t"): string
    {
        return $field['type'] == 'multi_select' ? "{$tabs}use-chips" : '';
    }

    protected function getJsFormFields(): string
    {
        return collect($this->getFieldsVisibleInForm())->map(function ($field) {
            $default = Field::hasDefault($field) ? json_encode($field['default']) : ($field['type'] == 'editor' ? "''" : 'null');
            $value = Field::isTranslatable($field) ? '{}' : (Field::isFrontArray($field) ? '[]' : $default);
            return "\n\t\t\t\t{$field['name']}: {$value}";
        })->implode(',');
    }

    protected function getJsFormFieldsValidation(): string
    {
        return collect($this->getFieldsVisibleInForm())->map(function ($field) {
            if (Field::isNullable($field) || !Field::hasValidation($field)) return '';
            return "\n\t\t\t\t{$field['name']}: 'required'";
        })->filter(fn ($rule) => !empty($rule))->implode(',');
    }

    protected function getJsFormFieldsTranslationInEdit(): string
    {
        return collect($this->getTranslatableFields())->map(function ($field) {
            return "\n\t\t\tthis.modelEdit.{$field['name']} = this.modelEdit.{$field['name']}_trans";
        })->implode('');
    }

    protected function getJsFormFieldsTranslationInCreate(): string
    {
        return collect($this->getTranslatableFields())->filter(fn ($field) => $field['type'] == 'editor')->map(function ($field) {
            return "\n\t\tthis.languages.forEach(code => {\n\t\t\tthis.form.{$field['name']}[code] = this.form.{$field['name']}[code] ?? '';\n\t\t})";
        })->implode('');
    }

}
