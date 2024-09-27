<?php

namespace W88\CrudSystem\Generators\Backend;

use W88\CrudSystem\Generators\BackendGenerator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use W88\CrudSystem\Facades\Field;

class LangGenerator extends BackendGenerator
{

    public function generate(): void
    {
        $this->ensureFileExists();
        $this->insertLang();
    }

    protected function getGeneratorDirectory(): string
    {
        return $this->modulePath . '/lang/en';
    }

    protected function getFilePath(): string
    {
        return $this->getGeneratorDirectory() . '/view.php';
    }

    protected function ensureFileExists(): void
    {
        $filePath = $this->getFilePath();
        if (!File::exists($filePath)) {
            File::put($filePath, "<?php\n\nreturn [\n\n];");
        }
    }

    protected function insertLang(): void
    {
        $filePath = $this->getFilePath();
        $contentFile = File::get($filePath);
        $contentTemplate = $this->getContentTemplate();
        if (strpos($contentFile, $contentTemplate) === false) {
            $pattern = '/\s*\];/';
            $comma = ',';
            if (preg_match('/,\s*\];/', $contentFile)) $pattern = '/,\s*\];/';
            if (preg_match('/return \[\s*\];/', $contentFile)) $comma = '';
            $contentFile = preg_replace($pattern, $comma . "\n\n\t". $contentTemplate . "\n];", $contentFile);
            File::put($filePath, $contentFile);
        }
    }

    protected function getContentTemplate(): string
    {
        $modelTitle = Str::title($this->modelNameSnakePlural);
        $content = "'{$this->modelNameSnake}_crud' => [\n\t\t'label' => '{$modelTitle}',";
        $content .= $this->getPermissionsTemplate();
        $content .= $this->getValidationTemplate();
        $content .= $this->getConstantsTemplate();
        return $content . "\n\t],\n";
    }

    protected function getPermissionsTemplate(): string
    {
        if (!$this->hasPermissions()) return '';
        $permissions = $this->getPermissionsTranslated();
        $permissionsResult = "\n\t\t'permissions' => [";
        $permissionsResult .= collect($permissions)->map(function ($permission, $name) {
            return "\n\t\t\t'{$name}' => '{$permission}',";
        })->join('');
        return $permissionsResult . "\n\t\t],";
    }

    protected function getValidationTemplate(): string
    {
        $validation = "\n\t\t'validation' => [";
        $validation .= collect($this->getNotHiddenFields())->map(function ($field, $name) {
            return "\n\t\t\t'{$name}' => '{$field['label']}',";
        })->join('');
        return $validation . "\n\t\t],";
    }

    protected function getConstantsTemplate(): string
    {
        $constantFields = $this->getConstantFields();
        if (empty($constantFields)) return '';
        $constants = "\n\t\t'constants' => [";
        foreach ($constantFields as $fieldName => $field) {
            $fieldName = strtolower(Str::snake($fieldName));
            $constants .= "\n\t\t\t'{$fieldName}' => [";
            foreach (Field::getOptions($field) as $key => $value) {
                $key = strtolower(Str::snake($key));
                $value = $value['label'] ?? $value;
                $constants .= "\n\t\t\t\t'{$key}' => '{$value}',";
            }
            $constants .= "\n\t\t\t],";
        }
        return $constants . "\n\t\t],";
    }

}
