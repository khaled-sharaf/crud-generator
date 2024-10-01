<?php

namespace W88\CrudSystem\Generators\Backend;

use W88\CrudSystem\Generators\BackendGenerator;
use Touhidurabir\StubGenerator\Facades\StubGenerator;
use W88\CrudSystem\Facades\Field;

class ResourceGenerator extends BackendGenerator
{

    public function generate(): void
    {
        $this->ensureStubExists();
        $this->ensureDirectoryExists();
        $this->generateResource();
    }

    protected function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/backend/resource.stub';
    }

    protected function getGeneratorDirectory(): string
    {
        return "{$this->modulePath}/app/Resources/{$this->versionNamespace}";
    }

    protected function getLocalResourceNamespace(): string
    {
        return $this->getResourceNamespace();
    }

    protected function generateResource(): void
    {
        StubGenerator::from($this->getStubPath(), true)
            ->to($this->getGeneratorDirectory())
            ->withReplacers($this->getReplacers())
            ->replace(true)
            ->as($this->getResourceName())
            ->save();
    }

    protected function getReplacers(): array
    {
        return [
            'CLASS_NAMESPACE' => $this->getLocalResourceNamespace(),
            'CLASS_NAME' => $this->getResourceName(),
            'FIELDS' => $this->getFieldsData(),
        ];
    }

    protected function getTimestampsFields(): string
    {
        return ",\n\t\t\t'created_at' => formatDate(\$this->created_at),\n\t\t\t'updated_at' => formatDate(\$this->updated_at)";
    }

    protected function getFieldsData(): string
    {
        return collect($this->getNotHiddenFields())->map(function ($field, $name) {
            $isTranslatable = Field::isTranslatable($field);
            $keyTrans = $isTranslatable ? ",\n\t\t\t'{$name}_trans' => \$this->getTranslations('{$name}')" : '';
            $value = "\$this->{$name}";
            $singleLookup = '';
            if (!Field::hasLookupFrontend($field) && Field::hasLookup($field)) {
                $lookup = "\\{$this->getConstantNamespace()}\\{$this->getConstantName($field)}";
                if (Field::isJson($field)) {
                    $value = "{$lookup}::getListForSelect(\$this->{$name})";
                } else {
                    $singleLookup = ",\n\t\t\t'{$name}_view' => {$lookup}::get(\$this->{$name})";
                }
            } else if (Field::isMultiFile($field)) {
                $value .= "Urls";
            } else if (Field::hasFile($field) && !Field::isMultiFile($field)) {
                $value .= "Url";
            }
            return "'$name' => {$value}{$keyTrans}{$singleLookup}";
        })->implode(",\n\t\t\t") . $this->getTimestampsFields();
    }

}
