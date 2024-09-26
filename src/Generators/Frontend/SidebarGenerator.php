<?php

namespace W88\CrudSystem\Generators\Frontend;

use W88\CrudSystem\Generators\Generator;
use Illuminate\Support\Facades\File;
use Touhidurabir\StubGenerator\Facades\StubGenerator;
use W88\CrudSystem\Traits\FrontendHelpersTrait;

class SidebarGenerator extends Generator
{
    use FrontendHelpersTrait;

    public function generate(): void
    {
        $this->ensureStubExists();
        $this->ensureDirectoryExists();
        $this->generateSidebar();
    }

    protected function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/frontend/sidebar.stub';
    }

    protected function getGeneratorDirectory(): string
    {
        return $this->getFrontendCrudPath();
    }

    protected function generateSidebar(): void
    {
        StubGenerator::from($this->getStubPath(), true)
        ->to($this->getGeneratorDirectory())
        ->withReplacers($this->getReplacers())
        ->replace(true)
        ->as('sidebar')
        ->ext('js')
        ->save();
    }

    protected function getReplacers(): array
    {
        return [
            'LINKS' => $this->getLinks(),
        ];
    }

    protected function getLinks(): string
    {
        $permissionList = $this->hasPermissions() ? "\n\t\t\tshowIf: app => app.\$can('view-list-{$this->modelNameKebab}')," : '';
        $permissionCreate = $this->hasPermissions() ? "\n\t\t\t\t\tshowIf: app => app.\$can('create-{$this->modelNameKebab}')," : '';
        $createTitle = $this->getPageTitle("create_{$this->modelNameSnake}");
        $createRoute = $this->checkApiRoute('create') && !$this->hasFormPopup() ? ",\n\t\t\tchildren: [
                {{$permissionCreate}
                    label: app => app.\$t('{$createTitle}'),
                    icon: 'add',
                    to: {name: '{$this->getCreateRouteName()}'},
                }
            ]" : '';
        
        return "{{$permissionList}
            label: app => app.\$t('{$this->getPageTitle('label')}}'),
            icon: 'list',
            to: {name: '{$this->getListRouteName()}'}{$createRoute}
        }";
    }

    protected function getPageTitle(string $title): string
    {
        return "{$this->frontendModuleName}.{$this->modelNameSnake}_crud.{$title}";
    }
    
}
