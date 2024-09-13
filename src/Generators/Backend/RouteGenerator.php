<?php

namespace W88\CrudSystem\Generators\Backend;


use W88\CrudSystem\Contracts\GeneratorInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RouteGenerator implements GeneratorInterface
{
    protected array $config;
    protected string $modelName;
    protected string $modulePath;
    protected ?string $module;
    protected ?string $version;

    public function __construct(array $config, string $modelName, string $modulePath, ?string $module = null, ?string $version = null)
    {
        $this->config = $config;
        $this->modelName = $modelName;
        $this->modulePath = $modulePath;
        $this->module = $module;
        $this->version = $version;
    }

    public function generate(): void
    {
        $routesPath = $this->getRoutesPath();
        $modulePrefix = $this->getModulePrefix();
        $apiPrefix = $this->getApiPrefix();
        $routesTemplate = $this->getRoutesTemplate($apiPrefix);

        $routesContent = $this->getRoutesContent($routesPath);

        $searchPatterns = $this->getSearchPatterns($modulePrefix);

        $this->insertRoute($routesContent, $searchPatterns, $routesTemplate, $routesPath);
    }

    protected function getRoutesPath(): string
    {
        return $this->modulePath . '/routes/' . $this->version . '/admin.php';
    }

    protected function getModulePrefix(): string
    {
        return Str::snake($this->module);
    }

    protected function getApiPrefix(): string
    {
        return strtolower(Str::plural($this->modelName));
    }

    protected function getRoutesTemplate(string $apiPrefix): string
    {
        return "Route::apiResource('{$apiPrefix}', '{$this->module}Controller');\n";
    }

    protected function getRoutesContent(string $routesPath): string
    {
        return File::get($routesPath);
    }

    protected function getSearchPatterns(string $modulePrefix): array
    {
        return [
            [
                "pattern" => "Route::middleware('dashboard')->group(function() {",
                "indentation" => "\t",
            ],
            [
                "pattern" => "Route::prefix('user')->group(function() {",
                "indentation" => "",
            ]
        ];
    }

    protected function insertRoute(string &$routesContent, array $searchPatterns, string $routesTemplate, string $routesPath): void
    {
        foreach ($searchPatterns as $pattern) {
            $startPos = strpos($routesContent, $pattern["pattern"]);
            if ($startPos !== false) {
                $groupStartPos = $this->findGroupStartPosition($routesContent, $startPos);
                $groupEndPos = $this->findClosingBracePosition($routesContent, $groupStartPos);

                if ($groupEndPos !== false) {
                    $routesContent = substr_replace($routesContent, "\t" . $routesTemplate . $pattern["indentation"], $groupEndPos, 0);
                    File::put($routesPath, $routesContent);
                    return;
                }
            }
        }

        File::append($routesPath, $routesTemplate);
    }

    protected function findGroupStartPosition(string $content, int $startPos): int
    {
        return strpos($content, '{', $startPos);
    }

    protected function findClosingBracePosition(string $content, int $startPos): ?int
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

        return null;
    }

}
