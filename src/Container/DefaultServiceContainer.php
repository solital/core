<?php

namespace Solital\Core\Container;

use Solital\Core\Container\Interface\{ContainerInterface, ServiceProviderInterface};
use Solital\Core\FileSystem\HandleFiles;

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
        /* $container->add('handler-file', function() {
            return new HandleFiles;
        }); */
        $container->add('handler-file', fn() => new HandleFiles);
    }
}
