<?php

namespace W88\CrudSystem\Generators\Backend;

use W88\CrudSystem\Generators\BackendGenerator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LookupGenerator extends BackendGenerator
{

    public function checkBeforeGenerate(): bool
    {
        return true;
    }
    
    public function generate(): void
    {
        $this->ensureFileExists();
        $this->insertLookup();
    }

    protected function getGeneratorDirectory(): string
    {
        return "{$this->modulePath}/config";
    }

    protected function getFilePath(): string
    {
        return $this->getGeneratorDirectory() . '/lookups.php';
    }

    protected function ensureFileExists(): void
    {
        $filePath = $this->getFilePath();
        if (!File::exists($filePath)) {
            File::put($filePath, "<?php\n");
        }
    }

    protected function insertLookup(): void
    {
        $filePath = $this->getFilePath();
        $contentFile = File::get($filePath);
        $lookups = $this->getLookups();
        if (count($lookups)) File::append($filePath, "\n");
        foreach ($lookups as $lookup) {
            if (strpos($contentFile, $lookup) === false) {
                File::append($filePath, "\n" . $lookup);
            }
        }
    }

    protected function getLookups(): array
    {
        $modelLookup = $this->getModelLookup();
        $lookups = [];
        if ($modelLookup) $lookups[] = $modelLookup;
        $lookups = array_merge($lookups, $this->getFieldLookups());
        return $lookups;
    }

    protected function getModelLookup(): string
    {
        $lookupRouteOption = $this->getLookupRouteOption();
        if (!$lookupRouteOption) return '';
        $labelColumn = is_string($lookupRouteOption) ? $lookupRouteOption : ($lookupRouteOption['label'] ?? null);
        $labelColumnPrint = $labelColumn ? "->labelColumn('{$labelColumn}')" : '';
        $valueColumn = $lookupRouteOption['value'] ?? null;
        $valueColumnPrint = $valueColumn ? "->valueColumn('{$valueColumn}')" : '';
        return "Lookup::register(\\{$this->modelNamespace}\\{$this->modelName}::class){$labelColumnPrint}{$valueColumnPrint};";
    }

    protected function getFieldLookups(): array
    {
        $lookups = [];
        foreach ($this->getLookupFields() as $name => $field) {
            $constantClass = '\\' . $this->getConstantNamespace() . '\\' . Str::studly($this->modelName . $name);
            if (class_exists($constantClass)) {
                $lookups[] = "Lookup::register({$constantClass}::class);";
            }
        }
        return $lookups;
    }

}
