<?php

namespace Solital\Components\Model;

use Solital\Core\Database\Create\Model;

class NameDefault extends Model
{
    /**
     * Construct
     */
    public function __construct()
    {
        $this->table = "";
        $this->primary_key = "";
        $this->columns = [];
    }
}
