<?php

namespace W88\CrudSystem\Factories;

class CrudGeneratorFactory
{
    public static function create($generators_action, $generator_type, $configData)
    {
        $className = 'W88\\CrudSystem\\Generators\\' . ucfirst($generators_action) . '\\' . ucfirst($generator_type) . 'Generator';
        if (!class_exists($className)) {
            throw new \InvalidArgumentException("Unknown generator type: {$generator_type}");
        }
        return new $className($configData);
    }
}
