<?php

namespace Solital\Core\Container;

use Solital\Core\Container\Interface\{ContainerInterface, ServiceProviderInterface};
use Solital\Core\FileSystem\HandleFiles;

class DefaultServiceContainer implements ServiceProviderInterface
{
    public function register(ContainerInterface $container)
    {
        $container->add('handler-file', function() {
            return new HandleFiles;
        });
    }
}
