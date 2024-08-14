<?php

namespace Solital\Core\Schedule\Attribute;

interface TimeInterface
{
    public function getAttributeName(): string;
    public function getArgs(): array;
}