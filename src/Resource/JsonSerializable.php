<?php

namespace Solital\Core\Resource;

class JsonSerializable implements \JsonSerializable
{
    /**
     * @var mixed
     */
    private $serializable;

    /**
     * @param mixed $serializable
     */
    public function __construct($serializable)
    {
        $this->serializable = $serializable;
    }
    
    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->serializable;
    }
}
