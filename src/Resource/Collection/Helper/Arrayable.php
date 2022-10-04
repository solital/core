<?php

namespace Solital\Core\Resource\Collection\Helper;

interface Arrayable
{
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array;
}
