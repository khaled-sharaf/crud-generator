<?php

namespace W88\CrudSystem\Facades;

use Illuminate\Support\Str;

class Field
{

    public static function getMigrationType(array $field): string
    {
        $fileFields = static::fileFields();
        $typeAfterRemovingMulti = str_replace('multi_', '', $field['type']);
        if (isset($field['migrationType'])) return $field['migrationType'];
        if (static::hasTranslatable($field)) return static::translatableFields()[$field['type']];
        if (in_array($field['type'], $fileFields)) return 'string';
        if (in_array($typeAfterRemovingMulti, $fileFields)) return 'json';
        if (isset($field['relation'])) {
            return static::isRelationConstrained($field) ? 'foreignId' : 'unsignedBigInteger';
        }
        return isset(static::types()[$field['type']]['migration']) ? static::types()[$field['type']]['migration'] : 'string';
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

    public static function isNullable(array $field): bool
    {
        return isset($field['nullable']) && $field['nullable'] === true;
    }

    public static function isBoolean(array $field): bool
    {
        return $field['type'] === 'boolean';
    }

    public static function isRelationConstrained(array $field): bool
    {
        return isset($field['relation']) && ($field['relation'] === true || (isset($field['relation']['constrained']) && $field['relation']['constrained'] === true));
    }

    public static function hasTranslatable(array $field): bool
    {
        return isset($field['translatable']) && $field['translatable'] === true && isset(static::translatableFields()[$field['type']]);
    }

    public static function hasFile(array $field): bool
    {
        $fileFields = static::fileFields();
        $typeAfterRemovingMulti = str_replace('multi_', '', $field['type']);
        return in_array($field['type'], $fileFields) || in_array($typeAfterRemovingMulti, $fileFields);
    }

    public static function hasConstant(array $field): bool
    {
        return isset($field['options']);
    }

    public static function hasLookup(array $field): bool
    {
        return self::hasConstant($field) && isset($field['lookup']) && $field['lookup'] === true;
    }

    public static function isFilterable(array $field): bool
    {
        return isset($field['filter']) && ($field['filter'] === true || in_array($field['filter'], ['single', 'multi']));
    }

    public static function isHidden(array $field): bool
    {
        return isset($field['hidden']) && (
            $field['hidden'] === true ||
            ($field['hidden']['list'] ?? false === true && $field['hidden']['create'] ?? false === true && $field['hidden']['update'] ?? false === true)
        );
    }

    public static function hasBoolean(array $field): bool
    {
        return $field['type'] === 'boolean' && isset($field['filter']) && $field['filter'] === true;
    }
    

    public static function hasBooleanFilter(array $field): bool
    {
        return $field['type'] === 'boolean' && isset($field['filter']) && $field['filter'] === true;
    }
    
    public static function hasFieldSingleConstant(array $field): bool
    {
        return in_array($field['type'], static::filterFields());
    }
    
    public static function hasFieldMultiConstant(array $field): bool
    {
        return in_array(str_replace('multi_', '', $field['type']), static::filterFields());
    }

    public static function hasFieldAllowedFilter(array $field): bool
    {
        return self::hasFieldSingleConstant($field) || self::hasFieldMultiConstant($field);
    }

    public static function hasConstantFilter(array $field): bool
    {
        return self::isFilterable($field) && self::hasFieldAllowedFilter($field) && self::hasConstant($field);
    }

    public static function hasBooleanRouteFilter(array $field): bool
    {
        return self::isBoolean($field) && isset($field['route']) && is_string($field['route']);
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

}