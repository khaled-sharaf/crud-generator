<?php

namespace W88\CrudSystem\Generators\Frontend;

use W88\CrudSystem\Generators\FrontendGenerator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use W88\CrudSystem\Facades\Field;

class LangGenerator extends FrontendGenerator
{

    public function generate(): void
    {
        $this->ensureFileExists();
        $this->insertLang();
    }

    protected function getGeneratorDirectory(): string
    {
        return $this->getFrontendModulePath() . '/lang';
    }

    protected function getFilePath(): string
    {
        return $this->getGeneratorDirectory() . '/en.js';
    }

    protected function ensureFileExists(): void
    {
        $filePath = $this->getFilePath();
        if (!File::exists($filePath)) {
            File::put($filePath, "export default {\n\n}");
        }
    }

    protected function insertLang(): void
    {
        $filePath = $this->getFilePath();
        $contentFile = File::get($filePath);
        $contentTemplate = $this->getContentTemplate();
        if (strpos($contentFile, $contentTemplate) === false) {
            $pattern = '/\s*\}$/';
            $comma = ',';
            if (preg_match('/,\s*\}$/', $contentFile)) $pattern = '/,\s*\}$/';
            if (preg_match('/export default \{\s*\}$/', $contentFile)) $comma = '';
            $contentFile = preg_replace($pattern, $comma . "\n\n\t". $contentTemplate . "\n}", $contentFile);
            File::put($filePath, $contentFile);
        }
    }

    protected function getContentTemplate(): string
    {
        $modelTitlePlural = Str::title($this->modelNameSnakePlural);
        $modelTitle = Str::title($this->modelNameSnake);
        $content = "{$this->modelNameSnake}_crud: {\n\t\tlabel: '{$modelTitle} List',";
        $content .= $this->hasSoftDeletes() ? "\n\t\ttrash_label: 'Trash of {$modelTitlePlural}'," : '';
        $content .= $this->checkApiRoute('create') ? "\n\t\tcreate_{$this->modelNameSnake}: 'Create {$modelTitle}'," : '';
        $content .= $this->checkApiRoute('edit') ? "\n\t\tedit_{$this->modelNameSnake}: 'Edit {$modelTitle}'," : '';
        $content .= $this->checkApiRoute('show') ? "\n\t\tview_{$this->modelNameSnake}: 'View {$modelTitle}'," : '';
        $content .= $this->getFormTemplate();
        $content .= $this->getLookupTemplate();
        return $content . "\n\t},\n";
    }

    protected function getFormTemplate(): string
    {
        $validation = "\n\t\ttable: {";
        $validation .= collect($this->getNotHiddenFields())->map(function ($field, $name) {
            return "\n\t\t\t{$name}: '{$field['label']}'";
        })->join(',');
        return $validation . "\n\t\t},";
    }

    protected function getLookupTemplate(): string
    {
        $constantFields = $this->getFieldsHasLookupFrontend();
        if (empty($constantFields)) return '';
        $constants = "\n\t\tlookups: {";
        foreach ($constantFields as $fieldName => $field) {
            $fieldName = strtolower(Str::snake($fieldName));
            $constants .= "\n\t\t\t{$fieldName}: {";
            foreach (Field::getOptions($field) as $key => $value) {
                $key = strtolower(Str::snake($key));
                $value = $value['label'] ?? $value;
                $constants .= "\n\t\t\t\t{$key}: '{$value}',";
            }
            $constants .= "\n\t\t\t},";
        }
        return $constants . "\n\t\t},";
    }

}
