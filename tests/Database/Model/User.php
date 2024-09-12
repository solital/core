<?php

namespace Solital\Test\Database\Model;

use Katrina\Katrina;

class User extends Katrina
{
    protected ?string $table = "users";
    protected ?bool $cache = false;
}
