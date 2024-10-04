<?php

namespace W88\CrudSystem\Generators\Frontend;

use W88\CrudSystem\Generators\FrontendGenerator;
use Touhidurabir\StubGenerator\StubGenerator;
use Illuminate\Support\Str;
use W88\CrudSystem\Facades\Field;

class ShowGenerator extends FrontendGenerator
{

    public function checkBeforeGenerate(): bool
    {
        return $this->checkApiRoute('show');
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
            'POPUP_WIDTH' => $this->getShowPopupWidth(),
            'DIALOG_NAME' => Str::camel($this->getShowFileName()),
            'DIALOG_TITLE' => $this->getLangPath("view_{$this->modelNameSnake}"),
            'SCRIPT' => $this->getScript(),
        ];
    }

    protected function getActions(): string
    {
        if (!$this->checkApiRoute('edit') || $this->hasFormPopup()) return '';
        $hasPermission = $this->hasPermissions() ? " v-if=\"\$can('edit-{$this->modelNameKebab}')\"" : '';
        return "<BtnEditTop{$hasPermission} :to=\"{ name: '{$this->getEditRouteName()}', params: { id: modelId } }\" />";
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
        return "<script src=\"./{$jsFileName}.js\"></script>";
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
            $fieldReplacers = array_merge($fieldReplacers, [
                'NAME_OF_FILE_ATTR_IN_MEDIA_VIEWER' => $this->getFileNameOfFileAttrInMediaViewer($field),
                'VALUE_OF_ITEM_IN_BADGE' => $this->getValueOfItemInBadge($field),
                'TITLE_TRUE' => $this->getTitleTrue($field),
                'TITLE_FALSE' => $this->getTitleFalse($field),
                'PRINT_VALUE_IN_TEXT' => $this->getPrintValueInText($field),
                'COL_SIZE' => $this->getColSize($field),
                'VALUE_CLASS' => $this->getValueClass($field),
            ]);
            $fields[] = (new StubGenerator())->from($this->getFieldsStubPath(Field::getStubViewFile($field)), true)
                ->withReplacers($fieldReplacers)->toString();
        }
        return implode("\n", $fields);
    }

    protected function getFileNameOfFileAttrInMediaViewer(array $field): string
    {
        return Field::isMultiFile($field) ? 'files' : 'file-one';
    }

    protected function getValueOfItemInBadge(array $field): string
    {
        $lookupName = $this->getLookupName($field['name']);
        $value = 'item';
        $relationLabel = Field::getLookupModelLabel($field);
        if (!Field::hasLookupFrontend($field) && Field::hasLookup($field) && Field::isJson($field)) $value = "item.label";
        if (Field::hasLookupFrontend($field)) $value = "{$lookupName}.getByValue(item)";
        if (Field::hasRelation($field)) $value = "item.{$relationLabel}";
        return $value;
    }

    protected function getPrintValueInText(array $field): string
    {
        $name = $field['name'];
        $showKey = Field::getKeyShowInFront($field);
        if (!Field::hasKeyShowInFront($field)) {
            $lookupName = $this->getLookupName($name);
            if (!Field::hasLookupFrontend($field) && Field::hasLookup($field) && !Field::isJson($field)) $showKey = "{$name}_view";
            if (Field::hasRelation($field)) {
                $relationName = Field::getRelationName($field);
                $label = Field::getLookupModelLabel($field);
                $showKey = "{$relationName}?.{$label}";
            }
        }
        $value = "model.{$showKey}";
        return Field::hasLookupFrontend($field) ? "{$lookupName}.getByValue($value)" : $value;
    }

    protected function getColSize(array $field): string
    {
        return Field::isMultiFile($field) ? '' : ' col-sm-6 col-md-4 col-lg-3 col-xl-2';
    }

    protected function getValueClass(array $field): string
    {
        return Field::isMultiFile($field) ? ' class="value"' : " :class=\"{value: !model.{$field['name']}}\"";
    }
}
