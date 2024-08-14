<?php

namespace Solital\Core\Schedule\Attribute;

use Attribute;
use DateTime;

#[Attribute(Attribute::TARGET_CLASS)]
class Daily implements TimeInterface
{
    public function __construct(
        private int|string $hour = 0,
        private int|string $minute = 0
    ) {}

    public function getAttributeName(): string
    {
        $var = explode('\\', $this::class);
        $className = array_pop($var);
        return lcfirst($className);
    }

    public function getArgs(): array
    {
        return [$this->hour, $this->minute];
    }
}
