<?php

namespace Solital\Test\Container\Dummy;

use Solital\Core\Container\Interface\ContainerInterface;
use Solital\Core\Container\Interface\ServiceProviderInterface;

class Service implements ServiceProviderInterface
{
    public function register(ContainerInterface $container)
    {
        $container->add('user', function ($container) {
            $mailer = $container->get('mailer');
            return new User($mailer);
        });

        $container->add('mailer', function () {
            return new Mail();
        });
    }
}
