<?php


namespace Khaled\CrudSystem\Contracts;

interface GeneratorInterface
{
    public function checkBeforeGenerate(): bool;
    
    public function generate(): void;
}
