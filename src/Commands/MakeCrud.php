<?php

namespace W88\CrudSystem\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module;
use Touhidurabir\StubGenerator\StubGenerator;
use W88\CrudSystem\Facades\Crud as FacadesCrud;
use W88\CrudSystem\Models\Crud;

class MakeCrud extends Command
{
    protected $signature = 'crud:make {name} {--module=}';
    protected $description = 'Generate a CRUD configuration file for a specified module and model';
    protected $module;
    protected $modelName;

    public function handle()
    {
        $this->module = $this->option('module');
        $crudName = strtolower(Str::snake($this->argument('name')));
        $this->modelName = Str::studly($crudName);
        $crudStubPath = __DIR__ . '/../stubs/crud.stub';
        $modulePath = base_path('Modules/' . $this->module . '/config/cruds');
        if (!$this->module) {
            $this->print('error', "Module name is required when generating a single CRUD.");
            return;
        }

        if (!File::exists($crudStubPath)) {
            $this->print('error', "Stub file not found at path: [{$crudStubPath}]");
            return;
        }

        if (!Module::has($this->module)) {
            $this->print('error', "Module [{$this->module}] not found.");
            return;
        }

        $crud = Crud::whereName($crudName)->whereModule($this->module)->first();
        if ($crud) {
            $this->print('error', "CRUD [{$crudName}] already exists.");
            return;
        }
        $fileName = $this->getFileName($crudName);
        FacadesCrud::formatCommandInfo($this, "Creating [{$crudName}] CRUD");
        $time = microtime(true) * 1000;
        (new StubGenerator)->from($crudStubPath, true)
        ->withReplacers($this->getReplacers())
        ->to($modulePath, true, true)
        ->as($fileName)
        ->save();
        Crud::newCrud($crudName, $fileName, $this->module);
        $time = (int) ((microtime(true) * 1000) - $time);
        $this->line(FacadesCrud::formatCommandRunGenerator($fileName, 'done', $time));
        $this->newLine();
    }

    protected function getReplacers(): array
    {
        return [
            'MODEL_NAME' => $this->modelName,
            'MODULE_NAME' => Str::camel($this->module),
        ];
    }

    public function getFileName(string $name): string
    {
        $name = "create_{$name}_crud";
        $time = now()->format('Y_m_d_His');
        return "{$time}_{$name}";
    }

    private function print(string $type, string $message): void
    {
        $this->$type("\n\n  {$message}\n");
        $this->newLine();
    }
}
