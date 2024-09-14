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
    protected $signature = 'fr:crud-make {name} {module} {--force}';
    protected $description = 'Generate a CRUD configuration file for a specified module and model';

    public function handle()
    {
        $module = $this->argument('module');
        $crudName = strtolower($this->argument('name'));
        $force = $this->option('force');
        $modelName = Str::studly($crudName);
        $crudStubPath = __DIR__ . '/../stubs/crud.stub';
        $modulePath = base_path('Modules/' . $module . '/config/cruds');
        $isCreateCrud = false;

        if (!File::exists($crudStubPath)) {
            $this->error("Stub file not found at path: {$crudStubPath}");
            return;
        }

        if (!Module::has($module)) {
            $this->error("Module not found: {$module}");
            return;
        }

        $crud = Crud::whereName($crudName)->whereModule($module)->first();
        if ($crud && !$force) {
            $this->error("Config file already exists at path: {$modulePath}/" . $crudName . '.php');
            return;
        }

        if ($force) {
            $sureForce = $this->confirm('Are you sure you want to delete the existing file?');
            if ($sureForce) {
                if ($crud->isGenerated) {
                    $this->error('This crud is already created, please delete it first');
                    return;
                }
                $isCreateCrud = true;
            } else {
                $this->info("Config file for {$crudName} in module {$module} not created.");
                return;
            }
        } else {
            $isCreateCrud = true;
        }

        if ($isCreateCrud) {
            $this->info("Creating config file for {$crudName} in module {$module}.");
            StubGenerator::from($crudStubPath, true)
                        ->withReplacers($this->getReplacers($modelName))
                        ->to($modulePath, true, true)
                        ->as($crudName)
                        ->replace($force)
                        ->save();
            if (!$crud) {
                Crud::newCrud($module, $crudName);
            }
            $this->info("Config file for {$crudName} in module {$module} created successfully.");
        }
    }

    protected function getReplacers(string $modelName): array
    {
        return [
            'MODEL_NAME' => $modelName
        ];
    }

}
