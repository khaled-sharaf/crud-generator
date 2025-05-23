<?php

namespace Khaled\CrudSystem\Generators\Frontend;

use Khaled\CrudSystem\Generators\FrontendGenerator;
use Touhidurabir\StubGenerator\StubGenerator;
use Illuminate\Support\Facades\File;
class SidebarGenerator extends FrontendGenerator
{

    public function checkBeforeGenerate(): bool
    {
        return $this->hasDashboardApi();
    }
    
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
        (new StubGenerator)->from($this->getStubPath(), true)
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
            'ORDER' => $this->getOrder(),
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
            label: app => app.\$t('{$this->getPageTitle('label')}'),
            icon: '{$this->getCrudIcon()}',
            to: {name: '{$this->getListRouteName()}'}{$createRoute}
        }";
    }

    protected function getPageTitle(string $title): string
    {
        return "{$this->frontendModuleName}.{$this->modelNameSnake}_crud.{$title}";
    }

    protected function getOrder(): string
    {
        $crudsPath = "{$this->getFrontendModulePath()}/cruds";
        return count(File::directories($crudsPath)) + 1;
    }
}
