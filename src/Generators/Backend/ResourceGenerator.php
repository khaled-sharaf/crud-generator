<?php

namespace W88\CrudSystem\Generators\Backend;

use W88\CrudSystem\Generators\BackendGenerator;
use Touhidurabir\StubGenerator\StubGenerator;
use W88\CrudSystem\Facades\Field;
use Illuminate\Support\Str;

class ResourceGenerator extends BackendGenerator
{

    public function checkBeforeGenerate(): bool
    {
        return true;
    }
    
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
        (new StubGenerator)->from($this->getStubPath(), true)
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
        return collect($this->getNotHiddenFields())
            ->map(function ($field, $name) {
                return $this->getFieldData($field, $name);
            })
            ->implode(",\n\t\t\t") . $this->getTimestampsFields();
    }

    protected function getFieldData(array $field, string $name): string
    {
        $value = $this->getBaseFieldValue($field, $name);
        $value = $this->applyTranslatableLogic($field, $name, $value);
        $value = $this->applyRelationLogic($field, $name, $value);
        $value = $this->applyLookupLogic($field, $name, $value);
        $value = $this->applyFileLogic($field, $name, $value);

        return "'$name' => $value";
    }

    protected function getBaseFieldValue(array $field, string $name): string
    {
        return "\$this->{$name}";
    }

    protected function applyTranslatableLogic(array $field, string $name, string $value): string
    {
        if (Field::isBackendTranslatable($field)) {
            return "request('__toForm') ? \$this->getTranslations('{$name}') : \$this->{$name}";
        }
        return $value;
    }

    protected function applyRelationLogic(array $field, string $name, string $value): string
    {
        if (Field::hasRelation($field)) {
            $relationName = Field::getRelationName($field);
            if (Field::hasLookupModel($field)) {
                return $this->getLookupModelRelationValue($field, $relationName, $value);
            } else {
                return $this->getRegularRelationValue($name, $relationName, $value);
            }
        }
        return $value;
    }

    protected function getLookupModelRelationValue(array $field, string $relationName, string $value): string
    {
        $lookupValue = Field::getLookupModelValue($field);
        $lookupLabel = Field::getLookupModelLabel($field);
        $relation = isset($this->getModelRelations()[$relationName]['type']) ? $this->getModelRelations()[$relationName] : null;

        if ($relation && in_array($relation['type'], ['belongsTo', 'hasOne'])) {
            return "{$value},\n\t\t\t'{$relationName}' => \$this->whenLoaded('{$relationName}') ? [
                '{$lookupValue}' => \$this->{$relationName}->{$lookupValue},
                '{$lookupLabel}' => \$this->{$relationName}->{$lookupLabel}
            ] : null";
        } elseif ($relation && in_array($relation['type'], ['belongsToMany', 'morphToMany'])) {
            $relationNameSingular = Str::singular($relationName);
            return "\$this->whenLoaded('{$relationName}') ? (request('__toForm') ? \$this->{$relationName}->pluck('{$lookupValue}') : \$this->{$relationName}->map(fn (\${$relationNameSingular}) => [
                '{$lookupValue}' => \${$relationNameSingular}->{$lookupValue},
                '{$lookupLabel}' => \${$relationNameSingular}->{$lookupLabel},
            ])) : []";
        } else {
            return "\$this->whenLoaded('{$relationName}')";
        }
    }

    protected function getRegularRelationValue(string $name, string $relationName, string $value): string
    {
        if ($relationName === $name) {
            return "\$this->whenLoaded('{$relationName}')";
        } else {
            return "{$value},\n\t\t\t'{$relationName}' => \$this->whenLoaded('{$relationName}')";
        }
    }

    protected function applyLookupLogic(array $field, string $name, string $value): string
    {
        if (!Field::hasLookupFrontend($field) && Field::hasLookup($field)) {
            $lookup = "\\{$this->getConstantNamespace()}\\{$this->getConstantName($field)}";
            if (Field::isJson($field)) {
                return "{$lookup}::getListForSelect(\$this->{$name})";
            } else {
                return "{$value},\n\t\t\t'{$name}_view' => {$lookup}::get(\$this->{$name})";
            }
        }
        return $value;
    }

    protected function applyFileLogic(array $field, string $name, string $value): string
    {
        if (Field::isMultiFile($field)) {
            return "{$value}Urls";
        } elseif (Field::hasFile($field) && !Field::isMultiFile($field)) {
            return "{$value}Url";
        }
        return $value;
    }

}
