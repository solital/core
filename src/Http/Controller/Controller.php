<?php

namespace Solital\Core\Http\Controller;

use Solital\Core\Wolf\Wolf;
use Solital\Core\Http\Controller\{
    BaseControllerTrait,
    HttpControllerTrait
};

abstract class Controller extends Wolf
{
    use BaseControllerTrait;
    use HttpControllerTrait;

    /**
     * @return void
     */
    public function removeParamsUrl(): void
    {
        $reflection = new \ReflectionFunction('remove_param');
        $reflection->invoke();
    }
}
