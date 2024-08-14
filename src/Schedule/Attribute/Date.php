<?php

namespace Solital\Core\Schedule\Attribute;

use Attribute;
use DateTime;

#[Attribute(Attribute::TARGET_CLASS)]
class Date implements TimeInterface
{
    public function __construct(private string|DateTime $date) {}

    public function getAttributeName(): string
    {
        $var = explode('\\', $this::class);
        $className = array_pop($var);
        return lcfirst($className);
    }

    public function getArgs(): array
    {
        return [$this->date];
    }
}
