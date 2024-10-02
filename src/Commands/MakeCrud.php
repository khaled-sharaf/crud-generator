<?php

namespace W88\CrudSystem\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module;
use Touhidurabir\StubGenerator\Facades\StubGenerator;
use W88\CrudSystem\Models\Crud;

class MakeCrud extends Command
{
    protected $signature = 'fr:crud-make {name} {module}';
    protected $description = 'Generate a CRUD configuration file for a specified module and model';
    protected $module;
    protected $modelName;

    public function handle()
    {
        $this->module = $this->argument('module');
        $crudName = strtolower($this->argument('name'));
        $this->modelName = Str::studly($crudName);
        $crudStubPath = __DIR__ . '/../stubs/crud.stub';
        $modulePath = base_path('Modules/' . $this->module . '/config/cruds');
        $isCreateCrud = false;

        if (!File::exists($crudStubPath)) {
            $this->error("Stub file not found at path: {$crudStubPath}");
            return;
        }

        if (!Module::has($this->module)) {
            $this->error("Module not found: {$this->module}");
            return;
        }

        $crud = Crud::whereName($crudName)->whereModule($this->module)->first();
        if ($crud) {
            $this->error("{$crudName} CRUD already exists");
            return;
        }
        $this->info("Creating config file for {$crudName} in module {$this->module}.");
        $fileName = $this->getFileName($crudName);
        StubGenerator::from($crudStubPath, true)
                    ->withReplacers($this->getReplacers())
                    ->to($modulePath, true, true)
                    ->as($fileName)
                    ->save();
        Crud::newCrud($crudName, $fileName, $this->module);
        $this->info("Config file for {$crudName} in module {$this->module} created successfully.");
    }

    protected function getReplacers(): array
    {
        return [
            'MODEL_NAME' => $this->modelName,
            'MODULE_NAME' => strtolower($this->module),
        ];
    }

    public function getFileName(string $name): string
    {
        $name = "create_{$name}_crud";
        $time = now()->format('Y_m_d_His');
        return "{$time}_{$name}";
    }
}
