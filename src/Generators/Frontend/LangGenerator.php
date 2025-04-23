<?php

namespace Khaled\CrudSystem\Generators\Frontend;

use Khaled\CrudSystem\Generators\FrontendGenerator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Khaled\CrudSystem\Facades\Field;

class LangGenerator extends FrontendGenerator
{

    public function checkBeforeGenerate(): bool
    {
        return $this->hasDashboardApi();
    }
    
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
        return $this->getGeneratorDirectory() . '/en.json';
    }

    protected function ensureFileExists(): void
    {
        $filePath = $this->getFilePath();
        if (!File::exists($filePath)) {
            File::put($filePath, "{}");
        }
    }

    protected function insertLang(): void
    {
        $filePath = $this->getFilePath();
        $contentFile = File::get($filePath);
        $contentFile = json_decode($contentFile, true);
        $newContent = $this->getContentTemplate();
        $contentFile = array_merge($contentFile, $newContent);
        File::put($filePath, json_encode($contentFile, JSON_PRETTY_PRINT));
    }

    protected function getContentTemplate(): array
    {
        $modelTitlePlural = Str::title(Str::replace('-', ' ', $this->modelNameKebabPlural));
        $modelTitle = Str::title(Str::replace('-', ' ', $this->modelNameKebab));
        $content = [
            'label' => "{$modelTitle} List",
        ];
        if ($this->hasSoftDeletes()) {
            $content['trash_label'] = "Trash of {$modelTitlePlural}";
        }
        if ($this->checkApiRoute('create')) {
            $content['create_' . $this->modelNameSnake] = "Create {$modelTitle}";
        }
        if ($this->checkApiRoute('edit')) {
            $content['edit_' . $this->modelNameSnake] = "Edit {$modelTitle}";
        }
        if ($this->checkApiRoute('show')) {
            $content['view_' . $this->modelNameSnake] = "View {$modelTitle}";
        }
        $content['table'] = $this->getFormTemplate();
        $content['lookups'] = $this->getLookupTemplate();
        return ["{$this->modelNameSnake}_crud" => $content];
    }

    protected function getFormTemplate(): array
    {
        $table = [];
        foreach ($this->getNotHiddenFields() as $name => $field) {
            $table[$name] = $field['label'];
        }
        return $table;
    }

    protected function getLookupTemplate(): array
    {
        $constantFields = $this->getFieldsHasLookupFrontend();
        if (empty($constantFields)) return [];
        $lookups = [];
        foreach ($constantFields as $fieldName => $field) {
            $fieldName = strtolower(Str::snake($fieldName));
            $lookups[$fieldName] = [];
            foreach (Field::getOptions($field) as $key => $value) {
                $key = Str::snake(strtolower($key));
                $value = $value['label'] ?? $value;
                $lookups[$fieldName][$key] = $value;
            }
        }
        return $lookups;
    }

}
