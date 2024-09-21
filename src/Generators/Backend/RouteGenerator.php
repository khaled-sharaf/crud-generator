<?php

namespace W88\CrudSystem\Generators\Backend;

use W88\CrudSystem\Generators\Generator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RouteGenerator extends Generator
{

    const METHODS = ['index', 'show', 'store', 'update', 'destroy'];

    public function generate(): void
    {
        $this->generateRoutes();
    }

    protected function getRoutesPath(): string
    {
        return $this->modulePath . '/routes/' . $this->version . '/admin.php';
    }

    protected function getUseController(): string
    {
        return "\nuse {$this->getControllerNamespace()}\\{$this->getControllerName()};";
    }

    protected function getContentFile(): string
    {
        return File::get($this->getRoutesPath());
    }

    protected function getExcludedOrOnlyMethods(): string
    {
        $methods = ['index'];
        if ($this->hasCreateRoute()) $methods[] = 'store';
        if ($this->hasUpdateRoute()) $methods[] = 'update';
        if ($this->hasProfileRoute()) $methods[] = 'show';
        if ($this->hasDeleteRoute()) $methods[] = 'destroy';
        if (count($methods) === count(self::METHODS)) return '';
        $diffMethods = array_diff(self::METHODS, $methods);
        $type = count($methods) > count($diffMethods) ? 'except' : 'only';
        $methods = $type === 'except' ? $diffMethods : $methods;
        $methods = collect($methods)->map(function($method) {
            return "'{$method}'";
        })->implode(', ');
        return "->{$type}({$methods})";
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
        foreach ($this->getBooleanRouteFields() as $field) {
            $routes[] = $this->getRouteBooleanTemplate($field);
        }
        return array_merge($routes, [$this->getRouteActivationTemplate(), $this->getRouteResourceTemplate()]);
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
        return "Route::patch('{$this->modelNameKebabPlural}/{id}/{$field['route']}', [{$this->getControllerName()}::class, '{$method}']);\n";
    }

    protected function putToFile($content, $method = 'put'): void
    {
        File::$method($this->getRoutesPath(), $content);
    }

    protected function getSearchPatterns(): array
    {
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
