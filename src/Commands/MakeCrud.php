<?php

namespace W88\CrudSystem\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Touhidurabir\StubGenerator\Facades\StubGenerator;

class MakeCrud extends Command
{
    protected $signature = 'fr:crud-make {module} {name}';
    protected $description = 'Generate a CRUD configuration file for a specified module and model';

    public function handle()
    {
        $module = $this->argument('module');
        $name = $this->argument('name');
        $modelName = Str::studly($name);

        $crudStubPath = base_path('W88/CrudSystem/stubs/crud.stu');
        if (!File::exists($crudStubPath)) {
            $this->error("Stub file not found at path: {$crudStubPath}");
            return;
        }

        // Define paths
        $modulePath = base_path('Modules/' . $module . '/config/cruds');

        StubGenerator::from($crudStubPath, true)
            ->to($modulePath, true, true)
            ->as(strtolower($modelName))
            ->replace(true)
            ->save();

        $this->info("Config file for {$modelName} in module {$module} generated successfully.");
    }
}
