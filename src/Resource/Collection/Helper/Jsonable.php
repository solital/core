<?php

namespace Solital\Core\Resource\Collection\Helper;

interface Jsonable
{
    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * 
     * @return string
     */
    public function toJson(int $options = 0): string;
}
