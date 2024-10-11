<?php

namespace W88\CrudSystem\Generators\Frontend;

use W88\CrudSystem\Generators\FrontendGenerator;
use Illuminate\Support\Facades\File;
use Touhidurabir\StubGenerator\StubGenerator;

class RouteGenerator extends FrontendGenerator
{

    public function checkBeforeGenerate(): bool
    {
        return $this->hasDashboardApi();
    }
    
    public function generate(): void
    {
        $this->ensureStubExists();
        $this->ensureDirectoryExists();
        $this->generateRoute();
    }

    protected function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/frontend/routeItem.stub';
    }

    protected function getGeneratorDirectory(): string
    {
        return $this->getFrontendCrudPath();
    }

    protected function generateRoute(): void
    {
        $routes = [$this->getListRoute()];
        if ($this->checkApiRoute('create') && !$this->hasFormPopup()) $routes[] = $this->getCreateRoute();
        if ($this->checkApiRoute('edit') && !$this->hasFormPopup()) $routes[] = $this->getEditRoute();
        if ($this->checkApiRoute('show') && !$this->hasShowPopup()) $routes[] = $this->getShowRoute();
        $this->generateRouteFile($routes);
    }

    protected function generateRouteFile(array $routes): void
    {
        $content = "export default [\n" . implode("\n\n", $routes) . "\n];";
        File::put($this->getGeneratorDirectory() . '/routes.js', $content);
    }

    protected function getRouteItem(array $replacers): string
    {
        return (new StubGenerator)->from($this->getStubPath(), true)->to($this->getGeneratorDirectory())->withReplacers($replacers)->toString();
    }

    protected function getListRoute(): string
    {
        return $this->getRouteItem([
            'ROUTE_COMMENT_NAME' => "{$this->modelName} List",
            'ROUTE_PATH' => "{$this->modelNameKebab}-list",
            'ROUTE_NAME' => $this->getListRouteName(),
            'COMPONENT_NAME' => $this->getListFileName(),
            'PAGE_TITLE' => $this->getPageTitle('label'),
            'ROUTE_PERMISSION' => $this->getPermission('view-list'),
        ]);
    }

    protected function getCreateRoute(): string
    {
        return $this->getRouteItem([
            'ROUTE_COMMENT_NAME' => "Create {$this->modelName}",
            'ROUTE_PATH' => "{$this->modelNameKebab}-create",
            'ROUTE_NAME' => $this->getCreateRouteName(),
            'COMPONENT_NAME' => $this->getCreateFileName(),
            'PAGE_TITLE' => $this->getPageTitle("create_{$this->modelNameSnake}"),
            'ROUTE_PERMISSION' => $this->getPermission('create'),
        ]);
    }

    protected function getEditRoute(): string
    {
        return $this->getRouteItem([
            'ROUTE_COMMENT_NAME' => "Edit {$this->modelName}",
            'ROUTE_PATH' => "{$this->modelNameKebab}-edit/:id",
            'ROUTE_NAME' => $this->getEditRouteName(),
            'COMPONENT_NAME' => $this->getEditFileName(),
            'PAGE_TITLE' => $this->getPageTitle("edit_{$this->modelNameSnake}"),
            'ROUTE_PERMISSION' => $this->getPermission('edit'),
        ]);
    }

    protected function getShowRoute(): string
    {
        return $this->getRouteItem([
            'ROUTE_COMMENT_NAME' => "View {$this->modelName}",
            'ROUTE_PATH' => "{$this->modelNameKebab}-view/:id",
            'ROUTE_NAME' => $this->getShowRouteName(),
            'COMPONENT_NAME' => $this->getShowFileName(),
            'PAGE_TITLE' => $this->getPageTitle("view_{$this->modelNameSnake}"),
            'ROUTE_PERMISSION' => $this->getPermission('view'),
        ]);
    }

    protected function getPageTitle(string $title): string
    {
        return "{$this->frontendModuleName}.{$this->modelNameSnake}_crud.{$title}";
    }

    protected function getPermission(string $permission): string
    {
        return $this->hasPermissions() ? "\n\t\t\tpermission: '{$permission}-{$this->modelNameKebab}'" : '';
    }
    
}
