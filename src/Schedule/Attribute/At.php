<?php

namespace Solital\Core\Schedule\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class At implements TimeInterface
{
    public function __construct(private string $expression) {}

    public function getAttributeName(): string
    {
        $var = explode('\\', $this::class);
        $className = array_pop($var);
        return lcfirst($className);
    }

    public function getArgs(): array
    {
        return [$this->expression];
    }
}
