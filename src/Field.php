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
        return static::types()[$field['type']] ?? 'string';
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

    public static function types(): array
    {
        return array_merge(static::normalFields(), static::jsonFields());
    }

    public static function normalFields(): array
    {
        return [
            'text' => 'string',
            'number' => 'string',
            'password' => 'string',
            'textarea' => 'text',
            'editor' => 'mediumText',
            'color' => 'string',
            'boolean' => 'boolean',
            'time' => 'time',
            'date' => 'date',
            'datetime' => 'dateTime',
            'image' => 'string',
            'video' => 'string',
            'file' => 'string',
            'checkbox' => 'string',
            'radio' => 'string',
            'select' => 'string',
            'slider' => 'string',
        ];
    }

    public static function jsonFields(): array
    {
        return [
            'range_date' => 'array',
            'multi_date' => 'array',
            'multi_range_date' => 'array',
            'multi_image' => 'array',
            'multi_video' => 'array',
            'multi_file' => 'array',
            'multi_checkbox' => 'array',
            'multi_select' => 'array',
            'range' => 'array',
            'array' => 'array',
            'location' => 'array',
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