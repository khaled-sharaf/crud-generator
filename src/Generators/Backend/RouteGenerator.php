<?php

namespace W88\CrudSystem\Generators\Backend;

use W88\CrudSystem\Generators\BackendGenerator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use W88\CrudSystem\Facades\Crud;

class RouteGenerator extends BackendGenerator
{
    
    protected $routeApiType = 'dashboard';

    public function generate(): void
    {
        if ($this->routeApiType === 'client' && !$this->hasClientApi()) return;
        $this->generateRoutes();
    }
    
    protected function getRouteFileName(): string
    {
        return $this->routeApiType === 'dashboard' ? 'admin.php' : 'api.php';
    }

    protected function getRoutesPath(): string
    {
        return "{$this->modulePath}/routes/{$this->version}/{$this->getRouteFileName()}";
    }

    protected function getControllerInRouteNamespace(): string
    {
        return $this->getControllerNamespace() . ($this->routeApiType === 'client' ? "\\{$this->clientDirectory}" : '');
    }

    protected function getUseController(): string
    {
        return "\nuse {$this->getControllerInRouteNamespace()}\\{$this->getControllerName()};";
    }

    protected function getContentFile(): string
    {
        return File::get($this->getRoutesPath());
    }

    protected function getRouteMethods(): array
    {
        $methods = [];
        if ($this->checkApiRoute('list', $this->routeApiType . 'Api') || $this->routeApiType === 'dashboard') $methods[] = 'index';
        if ($this->checkApiRoute('create', $this->routeApiType . 'Api')) $methods[] = 'store';
        if ($this->checkApiRoute('edit', $this->routeApiType . 'Api')) $methods[] = 'update';
        if ($this->checkApiRoute('show', $this->routeApiType . 'Api')) $methods[] = 'show';
        if ($this->checkApiRoute('delete', $this->routeApiType . 'Api')) $methods[] = 'destroy';
        return $methods;
    }

    protected function getExcludedOrOnlyMethods(): string
    {
        $methods = $this->getRouteMethods();
        if (count($methods) === count(Crud::config('generator.route_methods'))) return '';
        $diffMethods = array_diff(Crud::config('generator.route_methods'), $methods);
        $type = count($methods) > count($diffMethods) ? 'except' : 'only';
        $methods = $type === 'except' ? $diffMethods : $methods;
        $methods = collect($methods)->map(function($method) {
            return "'{$method}'";
        })->implode(', ');
        return $methods ? "->{$type}({$methods})" : '';
    }

    protected function generateRoutes(): void
    {
        $this->appendUseController();
        $this->appendRoutes();
    }

    protected function appendRoutes(): void
    {
        $content = $this->getContentFile();
        foreach ($this->getRoutes() as $route) {
            $routeIsExists = strpos($content, $route) !== false;
            $routeMatchPattern = false;
            if ($routeIsExists) continue;
            foreach ($this->getSearchPatterns() as $pattern) {
                $startPos = strpos($content, $pattern["pattern"]);
                if ($startPos === false) continue;
                $groupStartPos = $this->findGroupStartPosition($content, $startPos);
                $groupEndPos = $this->findClosingBracePosition($content, $groupStartPos);
                if ($groupEndPos === false) continue;
                $content = substr_replace($content, "\t{$route}{$pattern['indentation']}", $groupEndPos, 0);
                $this->putToFile($content);
                $routeMatchPattern = true;
                break;
            }
            if (!$routeMatchPattern) $this->putToFile($route, 'append');
        }
    }

    protected function appendUseController(): void
    {
        $content = $this->getContentFile();
        if (strpos($content, $this->getUseController()) !== false) return;
        $content = substr_replace($content, $this->getUseController(), strpos($content, '<?php ') + 5, 0);
        $this->putToFile($content);
    }

    protected function getRoutes(): array
    {
        $routes = [];
        if ($this->routeApiType === 'dashboard') {
            foreach ($this->getBooleanRouteFields() as $field) {
                $routes[] = $this->getRouteBooleanTemplate($field);
            }
            $routes[] = $this->getRouteActivationTemplate();
        }
        return array_merge($routes, [$this->getRouteResourceTemplate()]);
    }

    protected function getRouteResourceTemplate(): string
    {
        $methods = $this->getExcludedOrOnlyMethods();
        return "Route::apiResource('{$this->modelNameKebabPlural}', {$this->getControllerName()}::class){$methods};\n";
    }

    protected function getRouteActivationTemplate(): string
    {
        if (!$this->getActivationRouteOption()) return '';
        $middleware = $this->hasPermissions() ? "->middleware('can:activation-{$this->modelNameKebab}')" : '';
        return "Route::patch('{$this->modelNameKebabPlural}/{id}/activation', [{$this->getControllerName()}::class, 'activation']){$middleware};\n";
    }

    protected function getRouteBooleanTemplate(array $field): string
    {
        $method = Str::camel($field['route']);
        $routePath = strtolower(Str::kebab($field['route']));
        $middleware = $this->hasPermissions() ? "->middleware('can:{$routePath}-{$this->modelNameKebab}')" : '';
        return "Route::patch('{$this->modelNameKebabPlural}/{id}/{$routePath}', [{$this->getControllerName()}::class, '{$method}']){$middleware};\n";
    }

    protected function putToFile($content, $method = 'put'): void
    {
        File::$method($this->getRoutesPath(), $content);
    }

    protected function getSearchPatterns(): array
    {
        if ($this->routeApiType !== 'dashboard') return [];
        return [
            [
                "pattern" => "Route::middleware('dashboard')->group(function() {",
                "indentation" => "\t",
            ],
            [
                "pattern" => "Route::prefix('{$this->moduleNameKebab}')->group(function() {",
                "indentation" => "",
            ]
        ];
    }

    protected function findGroupStartPosition(string $content, int $startPos): int|false
    {
        return strpos($content, '{', $startPos);
    }

    protected function findClosingBracePosition(string $content, int $startPos): int|false
    {
        $openBraces = 0;
        $length = strlen($content);
        for ($i = $startPos; $i < $length; $i++) {
            if ($content[$i] === '{') {
                $openBraces++;
            } elseif ($content[$i] === '}') {
                $openBraces--;
                if ($openBraces === 0) {
                    return $i;
                }
            }
        }
        return false;
    }

}
