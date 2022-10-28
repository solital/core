<?php

namespace Solital\Core\Http\Controller;

use Solital\Core\Wolf\Wolf;
use Solital\Core\Http\Controller\BaseControllerTrait;
use Solital\Core\Http\Controller\HttpControllerTrait;

abstract class Controller extends Wolf
{
    use BaseControllerTrait;
    use HttpControllerTrait;
}
