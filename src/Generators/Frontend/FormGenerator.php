<?php

namespace Khaled\CrudSystem\Generators\Frontend;

use Khaled\CrudSystem\Generators\FrontendGenerator;
use Touhidurabir\StubGenerator\StubGenerator;
use Illuminate\Support\Str;
use Khaled\CrudSystem\Facades\Field;

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
            'SCRIPT' => $this->getScript(),
            'TRANSLATABLE_SELECT' => $this->getTranslatableSelect(),
            'KEEP_AFTER_SUBMIT' => $this->hasFormPopup() ? 'no-keep' : '',
            'FORM_NAME' => lcfirst($this->getFormFileName()),
        ];
    }

    protected function getTranslatableSelect(): string
    {
        $fields = collect($this->getFieldsVisibleInForm())->filter(fn ($field) => Field::isTranslatable($field));
        if ($fields->isEmpty()) return '';
        $formName = Str::camel($this->getFormFileName());
        return "<div class=\"px-7 pt-3 flex justify-end\">
                <TranslateSelect form=\"{$formName}\" />
            </div>";
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
            'FORM_NAME' => lcfirst($this->getFormFileName()),
            'FORMDATA_TYPE' => count($this->getFileFields()) ? 'Form' : '',
            'API_ROUTE_NAME' => $this->getApiRouteName(),
            'VALIDATION_FIELDS' => $this->getJsFormFieldsValidation(),
            'DECLARED_LOOKUPS' => $this->getJsDeclaredLookups(),
            'GET_LOOKUPS' => $this->getJsGetLookups()
        ];
    }

    protected function getJsDeclaredLookups(): string
    {
        return collect($this->getFieldsHasBackendLookupOnly())
        ->filter(fn ($field) => !Field::isHiddenEdit($field) && !Field::isHiddenCreate($field))
        ->map(function ($field) {
            $lookupName = Str::camel($this->getLookupName($field['name']));
            return "\n\t\t\t{$lookupName}: []";
        })->implode(",") . collect($this->getFieldsHasModelLookup())
        ->filter(fn ($field) => !Field::isHiddenEdit($field) && !Field::isHiddenCreate($field))
        ->map(function ($field) {
            $lookupName = Field::getLookupModelName($field);
            return "\n\t\t\t{$lookupName}: []";
        })->implode(",");
    }

    protected function getJsGetLookups(): string
    {
        return collect($this->getFieldsHasBackendLookupOnly())
        ->filter(fn ($field) => !Field::isHiddenEdit($field) && !Field::isHiddenCreate($field))
        ->map(function ($field) {
            $lookupName = Str::camel($this->getLookupName($field['name']));
            return "\n\t\tthis.{$lookupName} = await this.\$getLookup('{$this->getLookupApiRouteName($field['name'])}')";
        })->implode("") . collect($this->getFieldsHasModelLookup())
        ->filter(fn ($field) => !Field::isHiddenEdit($field) && !Field::isHiddenCreate($field))
        ->map(function ($field) {
            $routeName = Field::getLookupModelRouteName($field);
            $lookupName = Field::getLookupModelName($field);
            return "\n\t\tthis.{$lookupName} = await this.\$getLookup('{$routeName}')";
        })->implode("");
    }

    protected function getVueFormFields(): string
    {
        $fields = [];
        foreach ($this->getFieldsVisibleInForm() as $field) {
            $stubPath = $this->getFieldsStubPath(Field::getStubFormFile($field));
            $field['label'] = $this->getLangPath("table.{$field['name']}");
            $fieldReplacers = collect($field)->only('name', 'label')->mapWithKeys(fn ($value, $key) => [strtoupper($key) => $value])->toArray();
            $fieldReplacers = array_merge($fieldReplacers, [
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
                'CLASS_FIELD' => $this->getClassField($field),
                'SHOW_CONDITION' => $this->getShowCondition($field),
                'FORM_NAME' => lcfirst($this->getFormFileName()),
                'BOOLEAN_COLOR' => $this->getBooleanColor($field),
            ]);
            $fields[] = (new StubGenerator())->from($stubPath, true)->withReplacers($fieldReplacers)->toString();
        }
        return implode("\n", $fields);
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
        $formName = "{$tabs}formName=\"" . Str::camel($this->getFormFileName()) . '"';
        return Field::isTranslatable($field) ? "{$tabs}translatable{$formName}" : '';
    }

    protected function getFileType(array $field): string
    {
        return Field::hasFileImage($field) ? 'image' : (Field::hasFileVideo($field) ? 'video' : 'any');
    }
    
    protected function getMultiFileType(array $field, $tabs = "\n\t\t\t\t\t\t\t"): string
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
        $lookupBackend = Field::hasLookupModel($field) ? Field::getLookupModelName($field) : Str::camel($lookupName);
        return Field::hasLookupFrontend($field) ? $lookupName : $lookupBackend;
    }

    protected function getOptionsGroupType(array $field): string
    {
        return $field['type'] == 'multi_checkbox' ? 'checkbox' : 'radio';
    }

    protected function getIsMultiSelect(array $field, $tabs = "\n\t\t\t\t\t\t"): string
    {
        return $field['type'] == 'multi_select' ? "{$tabs}multiple" : '';
    }

    protected function getIsUseChips(array $field, $tabs = "\n\t\t\t\t\t\t"): string
    {
        return $field['type'] == 'multi_select' ? "{$tabs}use-chips" : '';
    }

    protected function getClassField(array $field): string
    {
        $class = 'col-12 col-md-6';
        if (Field::isFullWidth($field)) {
            $class = 'col-12';
        } else {
            if ($this->hasFormPopup()) {
                if (intval($this->getFormPopupWidth()) >= 600) {
                    $class = 'col-12 col-sm-6';
                } else {
                    $class = 'col-12';
                }
            }
        }
        return "{$class} col-padding";
    }

    protected function getShowCondition(array $field): string
    {
        $hiddenPage = Field::isHiddenEdit($field) ? 'edit' : (Field::isHiddenCreate($field) ? 'create' : null);
        return $hiddenPage ? " v-if=\"formType !== '{$hiddenPage}'\"" : '';
    }

    protected function getBooleanColor(array $field): string
    {
        return $field['name'] == 'activation' ? 'green-7' : 'primary';
    }

    protected function getJsFormFields(): string
    {
        return collect($this->getFieldsVisibleInForm())->map(function ($field) {
            $default = Field::hasDefault($field) && (Field::isBoolean($field) || Field::hasConstant($field)) ? json_encode($field['default']) : ($field['type'] == 'editor' ? "''" : 'null');
            $value = Field::isBackendTranslatable($field) ? '{}' : (Field::isFrontArray($field) ? '[]' : $default);
            return "\n\t\t\t\t{$field['name']}: {$value}";
        })->implode(',');
    }

    protected function getJsFormFieldsValidation(): string
    {
        return collect($this->getFieldsVisibleInForm())->map(function ($field) {
            $beforeValidation = "\n\t\t\t\t// ";
            $validationValueFormat = $this->getValidationValueFormat($field);
            return "{$beforeValidation}{$field['name']}: {$validationValueFormat}";
        })->filter(fn ($rule) => !empty($rule))->implode(',');
    }

    protected function getValidationValueFormat(array $field): string
    {
        $validationType = Field::getValidationType($field);
        if ($validationType == 'array') {
            return "this.\$vt.array(this.form.{$field['name']}.length, 'required')";
        } else if ($validationType == 'array_of_object') {
            return "this.\$vt.array(this.form.{$field['name']}.length, this.\$vt.object({
                //     key1: 'required',
                //     key2: this.\$vt.object(this.\$vt.validationTranslatableKey(['max:250'], true)), // if translatable
                // }))";
        } else if ($validationType == 'translatable') {
            return "this.\$vt.object(this.\$vt.validationTranslatableKey(['max:250'], true))";
        } else if ($validationType == 'object') {
            return "this.\$vt.object({
                //     key1: 'required',
                //     key2: this.\$vt.object(this.\$vt.validationTranslatableKey(['max:250'], true)), // if translatable
                // })";
        } else {
            return "'required'";
        }
    }

}
