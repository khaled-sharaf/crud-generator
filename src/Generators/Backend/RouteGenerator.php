<?php

namespace W88\CrudSystem\Generators\Backend;

use W88\CrudSystem\Generators\Generator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RouteGenerator extends Generator
{

    const METHODS = [
        'index', 'show', 'store', 'update', 'destroy'
    ];

    public function generate(): void
    {
        $this->insertRoute();
    }

    protected function getRoutesPath(): string
    {
        return $this->modulePath . '/routes/' . $this->version . '/admin.php';
    }

    protected function getUseController(): string
    {
        return "\nuse {$this->getControllerNamespace()}\\{$this->getControllerName()};";
    }

    protected function getRoutesContentFile(): string
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

    protected function getRouteResourceTemplate(): string
    {
        $methods = $this->getExcludedOrOnlyMethods();
        return "Route::apiResource('{$this->modelNameKebabPlural}', {$this->getControllerName()}::class){$methods};\n";
    }

    protected function getRouteActivationTemplate(): string
    {
        if (!$this->hasActivationRoute()) return "";
        $middleware = $this->hasPermissions() ? "->middleware('can:activation-{$this->modelNameKebab}')" : '';
        return "Route::patch('{$this->modelNameKebabPlural}/{id}/activation', [{$this->getControllerName()}::class, 'activation']){$middleware};\n";
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

    protected function insertRoute(): void
    {
        $routesPath = $this->getRoutesPath();
        $routesContentFile = $this->getRoutesContentFile();
        $routeResourceTemplate = $this->getRouteResourceTemplate();
        $routeActivationTemplate = $this->getRouteActivationTemplate();
        $routesContentFile = $this->addUseController($routesContentFile);
        $addActivationRoute = strpos($routesContentFile, $routeActivationTemplate) === false;
        $addResourceRoute = strpos($routesContentFile, $routeResourceTemplate) === false;
        foreach ($this->getSearchPatterns() as $pattern) {
            $startPos = strpos($routesContentFile, $pattern["pattern"]);
            if ($startPos !== false) {
                $groupStartPos = $this->findGroupStartPosition($routesContentFile, $startPos);
                $groupEndPos = $this->findClosingBracePosition($routesContentFile, $groupStartPos);
                if ($groupEndPos !== false) {
                    if ($addResourceRoute) {
                        $routesContentFile = substr_replace($routesContentFile, "\t" . $routeResourceTemplate . $pattern["indentation"], $groupEndPos, 0);
                    }
                    if ($addActivationRoute) {
                        $routesContentFile = substr_replace($routesContentFile, "\t" . $routeActivationTemplate . $pattern["indentation"], $groupEndPos, 0);
                    }
                    File::put($routesPath, $routesContentFile);
                    return;
                }
            }
        }
        if ($addActivationRoute) {
            File::append($routesPath, $routeActivationTemplate);
        }
        if ($addResourceRoute) {
            File::append($routesPath, $routeResourceTemplate);
        }
    }

    protected function addUseController(string $routesContentFile): string
    {
        if (strpos($routesContentFile, $this->getUseController()) !== false) return $routesContentFile;
        $routesContentFile = substr_replace($routesContentFile, $this->getUseController(), 5, 0);
        File::put($this->getRoutesPath(), $routesContentFile);
        return $routesContentFile;
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
