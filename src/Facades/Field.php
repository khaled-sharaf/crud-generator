<?php

namespace Khaled\CrudSystem\Facades;

use Illuminate\Support\Str;

class Field
{

    public static function getMigrationType(array $field): string
    {
        if (isset($field['migrationType'])) return $field['migrationType'];
        if (static::isTranslatable($field)) return static::translatableFields()[$field['type']];
        if (self::isMultiFile($field)) return 'json';
        if (isset($field['relation'])) {
            return static::isRelationConstrained($field) ? 'foreignId' : 'unsignedBigInteger';
        }
        return isset(static::types()[$field['type']]['migration']) ? static::types()[$field['type']]['migration'] : 'string';
    }

    public static function getMigrationParams(array $field): array
    {
        return $field['migrationParams'] ?? [];
    }

    public static function getSeederType(array $field): string
    {
        // try {
        //     $method = Str::camel($field['name']);
        //     $result = fake()->{$method}();
        //     $method = $result ? "fake()->{$method}()" : null;
        // } catch (\Exception $e) {
        //     $method = null;
        // }
        $method = $field['name'] == 'email' ? 'fake()->email()' : (Str::endsWith($field['name'], '_id') ? 'fake()->numberBetween(1, 10)' : null);
        return $method ?? (isset(static::types()[$field['type']]['seeder']) ? static::types()[$field['type']]['seeder'] : 'fake()->text(20)');
    }

    public static function getStubViewFile(array $field): string
    {
        return isset(static::types()[$field['type']]['stub_view']) ? static::types()[$field['type']]['stub_view'] : 'text';
    }

    public static function getStubFormFile(array $field): string
    {
        return isset(static::types()[$field['type']]['stub_form']) ? static::types()[$field['type']]['stub_form'] : 'text';
    }

    public static function isNullable(array $field): bool
    {
        return isset($field['nullable']) && $field['nullable'] === true;
    }

    public static function isUnique(array $field): bool
    {
        return isset($field['unique']) && $field['unique'] === true;
    }

    public static function hasDefault(array $field): bool
    {
        return isset($field['default']) && $field['default'] !== null;
    }

    public static function isBoolean(array $field): bool
    {
        return $field['type'] === 'boolean';
    }

    public static function isRelationConstrained(array $field): bool
    {
        return isset($field['relation']) && ($field['relation'] === true || (isset($field['relation']['constrained']) && $field['relation']['constrained'] === true));
    }

    public static function hasRelation(array $field): bool
    {
        return isset($field['relation']);
    }

    public static function getRelationName(array $field): string
    {
        return Str::camel(str_replace('_id', '', $field['name']));
    }

    public static function getRelationModel(array $field): string|null
    {
        return $field['relation']['model'] ?? null;
    }

    public static function getRelationType(array $field): string
    {
        return $field['relation']['type'] ?? 'belongsTo';
    }

    public static function isTranslatable(array $field): bool
    {
        return isset($field['translatable']) && $field['translatable'] === true && isset(static::translatableFields()[$field['type']]);
    }

    public static function isBackendTranslatable(array $field): bool
    {
        return isset($field['translatable']) && $field['translatable'] === true && $field['type'] !== 'array' && isset(static::translatableFields()[$field['type']]);
    }

    public static function isJson(array $field): bool
    {
        return isset($field['type']) && array_key_exists($field['type'], static::jsonFields());
    }

    public static function isFrontArray(array $field): bool
    {
        return self::isTranslatable($field) || self::isJson($field);
    }

    public static function hasFile(array $field): bool
    {
        $fileFields = static::fileFields();
        $typeAfterRemovingMulti = str_replace('multi_', '', $field['type']);
        return in_array($field['type'], $fileFields) || in_array($typeAfterRemovingMulti, $fileFields);
    }

    public static function isMultiFile(array $field): bool
    {
        return self::hasFile($field) && Str::startsWith($field['type'], 'multi_');
    }

    public static function hasFileImage(array $field): bool
    {
        $typeAfterRemovingMulti = str_replace('multi_', '', $field['type']);
        return self::hasFile($field) && $typeAfterRemovingMulti == 'image';
    }

    public static function hasFileVideo(array $field): bool
    {
        $typeAfterRemovingMulti = str_replace('multi_', '', $field['type']);
        return self::hasFile($field) && $typeAfterRemovingMulti == 'video';
    }

    public static function hasFileAnyType(array $field): bool
    {
        $typeAfterRemovingMulti = str_replace('multi_', '', $field['type']);
        return self::hasFile($field) && $typeAfterRemovingMulti == 'file';
    }

    public static function hasConstant(array $field): bool
    {
        return isset($field['options']);
    }

    public static function hasLookup(array $field): bool
    {
        return self::hasConstant($field) && isset($field['lookup']) && $field['lookup'] === true;
    }

    public static function hasLookupFrontend(array $field): bool
    {
        return self::hasConstant($field) && isset($field['lookupFrontend']) && $field['lookupFrontend'] === true;
    }
    
    public static function hasFilterRelation(array $field): bool
    {
        return isset($field['filterRelationName']);
    }
    
    public static function getFilterRelation(array $field): string
    {
        $relationName = Str::camel(str_replace('_id', '', $field['name']));
        return $field['filterRelationName'] ?? $relationName;
    }

    public static function getFilterRelationColumnName(array $field): string
    {
        return $field['filterRelationColumnName'] ?? 'id';
    }
    
    public static function hasLookupModel(array $field): bool
    {
        return isset($field['lookupModel']);
    }

    public static function getLookupModelRouteName(array $field): string
    {
        return is_string($field['lookupModel']) ? $field['lookupModel'] : Str::kebab(Str::singular(str_replace('_id', '', $field['name']))) . '-list';
    }

    public static function getLookupModelName(array $field): string
    {
        return Str::camel(str_replace('_id', '', $field['name']) . 'Lookup');
    }

    public static function getLookupModelValue(array $field): string
    {
        return $field['lookupModelValue'] ?? 'id';
    }

    public static function getLookupModelLabel(array $field): string
    {
        return $field['lookupModelLabel'] ?? 'name';
    }

    public static function hasKeyShowInFront(array $field): bool
    {
        return isset($field['keyShowInFront']);
    }

    public static function getKeyShowInFront(array $field): string
    {
        return $field['keyShowInFront'] ?? "{model}.{$field['name']}";
    }

    public static function isFilterable(array $field): bool
    {
        return isset($field['filter']) && ($field['filter'] === true || in_array($field['filter'], ['single', 'multi']));
    }

    public static function isNotDatabase(array $field): bool
    {
        return isset($field['notDatabase']) && $field['notDatabase'] === true;
    }

    public static function isHidden(array $field): bool
    {
        return isset($field['hidden']) && (
            $field['hidden'] === true ||
            (($field['hidden']['list'] ?? false) === true && ($field['hidden']['create'] ?? false) === true && ($field['hidden']['edit'] ?? false) === true && ($field['hidden']['show'] ?? false) === true)
        );
    }

    public static function isFullWidth(array $field): bool
    {
        return $field['frontend']['fullWidth'] ?? false;
    }

    public static function isVisibleList(array $field): bool
    {
        return $field['frontend']['visibleList'] ?? false;
    }

    public static function isSortable(array $field): bool
    {
        return $field['frontend']['sortable'] ?? false;
    }

    public static function isExportable(array $field): bool
    {
        return $field['frontend']['exportable'] ?? false;
    }

    public static function isSearchable(array $field): bool
    {
        return $field['frontend']['searchable'] ?? false;
    }

    public static function isAdvancedSearchable(array $field): bool
    {
        return $field['frontend']['advancedSearchable'] ?? false;
    }

    public static function isHiddenList(array $field): bool
    {
        return isset($field['hidden']) && ($field['hidden'] === true || ($field['hidden']['list'] ?? false) === true);
    }

    public static function isHiddenCreate(array $field): bool
    {
        return isset($field['hidden']) && ($field['hidden'] === true || ($field['hidden']['create'] ?? false) === true);
    }

    public static function isHiddenEdit(array $field): bool
    {
        return isset($field['hidden']) && ($field['hidden'] === true || ($field['hidden']['edit'] ?? false) === true);
    }

    public static function isHiddenShow(array $field): bool
    {
        return isset($field['hidden']) && ($field['hidden'] === true || ($field['hidden']['show'] ?? false) === true);
    }

    public static function hasBoolean(array $field): bool
    {
        return $field['type'] === 'boolean' && isset($field['filter']) && $field['filter'] === true;
    }

    public static function hasBooleanFilter(array $field): bool
    {
        return $field['type'] === 'boolean' && isset($field['filter']) && $field['filter'] === true;
    }

    public static function hasDateFilter(array $field): bool
    {
        return self::isDate($field) && isset($field['filter']) && $field['filter'] === true;
    }
    
    public static function isSingleConstant(array $field): bool
    {
        return in_array($field['type'], static::filterFields());
    }
    
    public static function isMultiConstant(array $field): bool
    {
        return in_array(str_replace('multi_', '', $field['type']), static::filterFields());
    }

    public static function hasFieldAllowedFilter(array $field): bool
    {
        return self::isSingleConstant($field) || self::isMultiConstant($field);
    }

    public static function hasConstantFilter(array $field): bool
    {
        return self::isFilterable($field) && self::hasFieldAllowedFilter($field) && self::hasConstant($field);
    }

    public static function hasApiRoute(array $field): bool
    {
        return isset($field['route']) && is_string($field['route']);
    }

    public static function hasBooleanRouteFilter(array $field): bool
    {
        return self::isBoolean($field) && self::hasApiRoute($field);
    }

    public static function hasValidation(array $field): bool
    {
        return collect($field)->filter(function ($value, $key) {
            return Str::startsWith($key, 'validation') && !empty($value);
        })->isNotEmpty();
    }

    public static function getValidationType(array $field): string
    {
        if ($field['type'] == 'array') {
            $validationType = 'array';
        } else if ($field['type'] == 'array_of_object') {
            $validationType = 'array_of_object';
        } else if (Field::isBackendTranslatable($field)) {
            $validationType = 'translatable';
        } else {
            $validationType = collect($field)->filter(fn ($value, $key) => self::hasValidation($field))->keys()->map(function ($key) {
                if (Str::endsWith($key, '.*')) {
                    return 'array';
                } else if (Str::contains($key, '.*.')) {
                    return 'array_of_object';
                } else if (Str::contains($key, '.')) {
                    return 'object';
                }
            })->first();
        }
        return $validationType ?? 'string';
    }

    public static function getOptions(array $field): array
    {
        return collect($field['options'])->filter(function ($value, $key) {
            return is_string($value) || (isset($value['label']) && isset($value['value']));
        })->toArray();
    }

    public static function getFilter(array $field)
    {
        if (!self::isFilterable($field)) return false;
        return $field['filter'] === true ? 'single' : $field['filter'];
    }

    public static function isDate(array $field)
    {
        return in_array($field['type'], self::dateFields());
    }

    public static function types(): array
    {
        return array_merge(static::normalFields(), static::jsonFields());
    }

    public static function normalFields(): array
    {
        return Crud::config('field.types.normal');
    }

    public static function jsonFields(): array
    {
        return Crud::config('field.types.json');
    }

    public static function translatableFields(): array
    {
        return Crud::config('field.types.translatable');
    }

    public static function fileFields(): array
    {
        return Crud::config('field.types.file');
    }

    public static function filterFields(): array
    {
        return Crud::config('field.types.filter');
    }

    public static function dateFields(): array
    {
        return Crud::config('field.types.dates');
    }

}