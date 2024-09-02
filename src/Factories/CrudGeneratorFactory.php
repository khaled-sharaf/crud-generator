<?php


// W88/CrudSystem/Factories/CrudGeneratorFactory.php

namespace App\CrudSystem\Factories;

class CrudGeneratorFactory
{
    public static function create($type, $config, $modelName, $modulePath, $moduleNamespace = null, $version = null)
    {
        $className = 'App\\CrudSystem\\Generators\\' . ucfirst($type) . 'Generator';

        if (!class_exists($className)) {
            throw new \InvalidArgumentException("Unknown generator type: {$type}");
        }

        return new $className($config, $modelName, $modulePath, $moduleNamespace, $version);
    }
}
