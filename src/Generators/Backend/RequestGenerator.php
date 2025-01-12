<?php

namespace Khaled\CrudSystem\Generators\Backend;

use Khaled\CrudSystem\Generators\BackendGenerator;
use Touhidurabir\StubGenerator\StubGenerator;
use Illuminate\Support\Str;
use Khaled\CrudSystem\Facades\Field;

class RequestGenerator extends BackendGenerator
{
    
    public function checkBeforeGenerate(): bool
    {
        return $this->checkApiRoute('create') || $this->checkApiRoute('edit');
    }
    
    public function generate(): void
    {
        $this->ensureStubExists();
        $this->ensureDirectoryExists();
        $this->generateRequest();
    }

    protected function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/backend/request.stub';
    }

    protected function getGeneratorDirectory(): string
    {
        return "{$this->modulePath}/app/Http/Requests/{$this->versionNamespace}";
    }

    protected function getLocalRequestNamespace(): string
    {
        return $this->getRequestNamespace();
    }

    protected function generateRequest(): void
    {
        (new StubGenerator)->from($this->getStubPath(), true)
            ->to($this->getGeneratorDirectory())
            ->withReplacers($this->getReplacers())
            ->replace(true)
            ->as($this->getRequestName())
            ->save();
    }

    protected function getReplacers(): array
    {
        $rules = $this->getRules();
        $rules = $this->getRules();
        return [
            'CLASS_NAMESPACE' => $this->getLocalRequestNamespace(),
            'CLASS_NAME' => $this->getRequestName(),
            'TRANSLATION_PATH' => "{$this->moduleNameSnake}::view.{$this->modelNameSnake}_crud.validation",
            'RULES' => $rules,
            'CUSTOM_RULES_WHEN_CREATE' => $this->getCustomRulesWhenCreate(),
            'CUSTOM_RULES_WHEN_UPDATE' => $this->getCustomRulesWhenUpdate(),
            'USE_CLASSES' => $this->getUseClasses($rules),
        ];
    }

    protected function getUseClasses(): string
    {
        $rules = implode(',', $this->getAllRules());
        if (strpos($rules, 'Rule::') !== false || strpos($rules, 'new Rule') !== false) {
            return "use Illuminate\Validation\Rule;\n";
        }
        return '';
    }

    protected function getAllRules(): array
    {
        $allRules = [];
        $rules = collect($this->getFieldsVisibleInForm())->map(fn($field, $name) =>  $this->handleFieldValidationRule($name, $field))->values()->all();
        foreach ($rules as $value) {
            foreach ($value as $key => $rule) {
                $allRules[$key] = $rule;
            }
        }
        return $allRules;
    }

    protected function handleFieldValidationRule(string $name, array $field): array
    {
        $rules = [];
        $validations = collect($field)->filter(fn($value, $key) => Str::startsWith($key, 'validation'))->toArray();
        if ($validations) {
            foreach ($validations as $key => $value) {
                $key = str_replace('validation', $name, $key);
                $rule = is_array($value) ? $this->handleArrayValidationRule($value) : "'{$value}'";
                $rules[$key] = $rule;
            }
        } else {
            if (Field::isUnique($field)) {
                $isNullable = Field::isNullable($field) ? 'nullable|' : '';
                $rules[$name] = "'{$isNullable}unique:{$this->modelNameSnakePlural}'";
            } else {
                $rules[$name] = "'nullable'";
            }
        }
        return $rules;
    }

    protected function handleArrayValidationRule(array $validation): string
    {
        return '[' . collect($validation)->map(fn($rule) => "\n\t\t\t\t" . ($this->isPhpCode($rule) ? $rule : "'{$rule}'"))->implode(',') . "\n\t\t\t]";
    }
    
    protected function getRules(): string
    {
        return collect($this->getAllRules())->filter(function ($rule, $name) {
            $field = $this->getFieldByName(Str::before($name, '.'));
            return ($field && !Field::isHiddenCreate($field) && !Field::isHiddenEdit($field));
        })->map(fn($rule, $name) => "\n\t\t\t'{$name}' => $rule")->implode(',');
    }

    protected function getCustomRulesWhenCreate(): string
    {
        $rules = collect($this->getAllRules())->filter(function ($rule, $name) {
            $field = $this->getFieldByName(Str::before($name, '.'));
            return $field && Field::isHiddenEdit($field) && !Field::isHiddenCreate($field);
        })->toArray();
        return $this->handleCustomRules($rules, 'POST');
    }

    protected function getCustomRulesWhenUpdate(): string
    {
        $rules = collect($this->getAllRules())->filter(function ($rule, $name) {
            $field = $this->getFieldByName(Str::before($name, '.'));
            return $field && Field::isHiddenCreate($field) && !Field::isHiddenEdit($field);
        })->toArray();
        return $this->handleCustomRules($rules, 'PUT');
    }

    protected function handleCustomRules(array $rules, string $method): string
    {
        $rulesString = '';
        if (count($rules)) {
            $rulesString = "\n\t\tif (\$this->isMethod('{$method}')) {";
            foreach ($rules as $name => $rule) {
                $rulesString .= "\n\t\t\t\$rules['{$name}'] = $rule;";
            }
            $rulesString .= "\n\t\t}";
        }
        return $rulesString;
    }
    
}
