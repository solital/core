<?php

namespace Solital\Core\Container;

use Solital\Core\Auth\Password;
use Solital\Core\Container\Interface\{ContainerInterface, ServiceProviderInterface};
use Solital\Core\FileSystem\HandleFiles;
use Solital\Core\Mail\Mailer;
use Solital\Core\Queue\EventLoop;
use Solital\Core\Resource\Message;
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
        $container->add('solital-mailer', fn() => new Mailer);
        $container->add('solital-message', fn() => new Message);
        $container->add('solital-eventloop', fn() => new EventLoop);
    }
}
