<?php

namespace Solital\Core\Container\tests;

use Solital\Core\Container\Interface\ContainerInterface;
use Solital\Core\Container\Interface\ServiceProviderInterface;

class ServiceTest implements ServiceProviderInterface
{
    public function register(ContainerInterface $container)
    {
        $container->add('user', function ($container) {
            $mailer = $container->get('mailer');
            return new UserTest($mailer);
        });

        $container->add('mailer', function () {
            return new MailTest();
        });
    }
}
