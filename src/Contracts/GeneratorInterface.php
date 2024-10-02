<?php


namespace W88\CrudSystem\Contracts;

interface GeneratorInterface
{
    public function checkBeforeGenerate(): bool;
    
    public function generate(): void;
}
