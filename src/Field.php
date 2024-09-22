<?php

namespace W88\CrudSystem;

use Illuminate\Support\Str;

class Field
{

    public static function getMigrationType(array $field): string
    {
        $typeAfterRemovingMulti = str_replace('multi_', '', $field['type']);
        if (isset($field['migrationType'])) return $field['migrationType'];
        if (static::hasTranslatable($field)) return static::translatableFields()[$field['type']];
        if (in_array($field['type'], static::fileFields())) return 'string';
        if (in_array($typeAfterRemovingMulti, static::fileFields())) return 'json';
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
        $method = $field['name'] == 'email' ? 'fake()->email()' : (Str::endsWith($field['name'], '_id') ? 'fake()->numberBetween(1, 100)' : null);
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
        $typeAfterRemovingMulti = str_replace('multi_', '', $field['type']);
        return in_array($field['type'], static::fileFields()) || in_array($typeAfterRemovingMulti, static::fileFields());
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
        return [
            'text' => ['migration' => 'string', 'seeder' => 'fake()->text(20)'],
            'number' => ['migration' => 'string', 'seeder' => 'fake()->numberBetween(1, 100)'],
            'password' => ['migration' => 'string', 'seeder' => "\Illuminate\Support\Facades\Hash::make('12345678')"],
            'textarea' => ['migration' => 'text', 'seeder' => 'fake()->text()'],
            'editor' => ['migration' => 'mediumText', 'seeder' => 'fake()->text()'],
            'color' => ['migration' => 'string', 'seeder' => 'fake()->hexColor()'],
            'boolean' => ['migration' => 'boolean', 'seeder' => 'fake()->boolean()'],
            'time' => ['migration' => 'time', 'seeder' => 'fake()->time()'],
            'date' => ['migration' => 'date', 'seeder' => 'fake()->date()'],
            'datetime' => ['migration' => 'dateTime', 'seeder' => 'fake()->dateTime()'],
            'timestamp' => ['migration' => 'timestamp', 'seeder' => 'fake()->dateTime()'],
            'image' => ['migration' => 'string', 'seeder' => 'null'],
            'video' => ['migration' => 'string', 'seeder' => 'null'],
            'file' => ['migration' => 'string', 'seeder' => 'null'],
            'checkbox' => ['migration' => 'string', 'seeder' => 'null'],
            'select' => ['migration' => 'string', 'seeder' => 'null'],
            'radio' => ['migration' => 'string', 'seeder' => 'null'],
        ];
    }

    public static function jsonFields(): array
    {
        return [
            'range_date' => ['migration' => 'json', 'seeder' => '[]'],
            'multi_date' => ['migration' => 'json', 'seeder' => '[]'],
            'multi_range_date' => ['migration' => 'json', 'seeder' => '[]'],
            'multi_image' => ['migration' => 'json', 'seeder' => '[]'],
            'multi_video' => ['migration' => 'json', 'seeder' => '[]'],
            'multi_file' => ['migration' => 'json', 'seeder' => '[]'],
            'multi_checkbox' => ['migration' => 'json', 'seeder' => '[]'],
            'multi_select' => ['migration' => 'json', 'seeder' => '[]'],
            'slider' => ['migration' => 'json', 'seeder' => '[]'],
            'range' => ['migration' => 'json', 'seeder' => '[]'],
            'array' => ['migration' => 'json', 'seeder' => '[]'],
            'location' => ['migration' => 'json', 'seeder' => '[]'],
        ];
    }

    public static function translatableFields(): array
    {
        return [
            'text' => 'json',
            'textarea' => 'json',
            'editor' => 'json',
            'array' => 'json',
        ];
    }

    public static function fileFields(): array
    {
        return [
            'image',
            'video',
            'file',
        ];
    }

    public static function filterFields(): array
    {
        return [
            'checkbox',
            'select',
            'radio',
        ];
    }

}