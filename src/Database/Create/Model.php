<?php

namespace Solital\Core\Database\Create;

use Solital\Core\Database\ORM;

/**
 * This Model offers a way to access Katrina ORM's attributes and 
 * classes without having to instantiate Katrina. 
 * 
 * This is an abstract class, so it should only be extended by other models. 
 */

abstract class Model
{
    /**
     * @var string
     */
    protected string $table;

    /**
     * @var string
     */
    protected string $primary_key;

    /**
     * @var array
     */
    protected array $columns;

    /**
     * @return ORM
     */
    protected function instance(): ORM
    {
        $katrina = new ORM($this->table, $this->primary_key, $this->columns);

        return $katrina;
    }
}
