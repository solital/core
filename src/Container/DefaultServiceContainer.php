<?php

namespace Solital\Core\Container;

use Solital\Core\Auth\Password;
use Solital\Core\Container\Interface\{ContainerInterface, ServiceProviderInterface};
use Solital\Core\FileSystem\HandleFiles;
use Solital\Core\Wolf\Wolf;

class DefaultServiceContainer implements ServiceProviderInterface
{
    /**
     * @param ContainerInterface $container
     * 
     * @return void
     */
    #[\Override]
    public function register(ContainerInterface $container)
    {
        $container->add('handler-file', fn() => new HandleFiles);
        $container->add('solital-password', fn() => new Password);
        $container->add('solital-wolf', fn() => new Wolf);
    }
}
