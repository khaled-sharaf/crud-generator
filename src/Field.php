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
        if (in_array($typeAfterRemovingMulti, static::fileFields())) return 'text';
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

    public static function getOptions(array $field): array
    {
        return collect($field['options'])->filter(function ($value, $key) {
            return is_string($value) || (isset($value['label']) && isset($value['value']));
        })->toArray();
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
            'image' => ['migration' => 'string', 'seeder' => 'null'],
            'video' => ['migration' => 'string', 'seeder' => 'null'],
            'file' => ['migration' => 'string', 'seeder' => 'null'],
            'checkbox' => ['migration' => 'string', 'seeder' => 'null'],
            'radio' => ['migration' => 'string', 'seeder' => 'null'],
            'select' => ['migration' => 'string', 'seeder' => 'null'],
        ];
    }

    public static function jsonFields(): array
    {
        return [
            'range_date' => ['migration' => 'array', 'seeder' => '[]'],
            'multi_date' => ['migration' => 'array', 'seeder' => '[]'],
            'multi_range_date' => ['migration' => 'array', 'seeder' => '[]'],
            'multi_image' => ['migration' => 'array', 'seeder' => '[]'],
            'multi_video' => ['migration' => 'array', 'seeder' => '[]'],
            'multi_file' => ['migration' => 'array', 'seeder' => '[]'],
            'multi_checkbox' => ['migration' => 'array', 'seeder' => '[]'],
            'multi_select' => ['migration' => 'array', 'seeder' => '[]'],
            'slider' => ['migration' => 'array', 'seeder' => '[]'],
            'range' => ['migration' => 'array', 'seeder' => '[]'],
            'array' => ['migration' => 'array', 'seeder' => '[]'],
            'location' => ['migration' => 'array', 'seeder' => '[]'],
        ];
    }

    public static function translatableFields(): array
    {
        return [
            'text' => 'text',
            'textarea' => 'mediumText',
            'editor' => 'longText',
            // 'array' => 'mediumText',
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

}