<?php

namespace Solital\Core\Container;

use Solital\Core\Auth\Password;
use Solital\Core\FileSystem\HandleFiles;
use Solital\Core\Kernel\Application;
use Solital\Core\Mail\Mailer;
use Solital\Core\Queue\EventLoop;
use Solital\Core\Wolf\Wolf;
use Solital\Core\Resource\{JSON, Message};
use Solital\Core\Container\Interface\{ContainerInterface, ServiceProviderInterface};

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
        $yaml = Application::yamlParse("bootstrap.yaml");

        $container->add("handler-file", fn() => new HandleFiles);
        $container->add("solital-password", fn() => new Password);
        $container->add("solital-wolf", fn() => new Wolf);
        $container->add("solital-mailer", fn() => new Mailer);
        $container->add("solital-message", fn() => new Message);
        $container->add("solital-eventloop", fn() => new EventLoop);
        $container->add("solital-json", $container->factory(function () use ($yaml) {
            $json = new JSON();
            if (array_key_exists("json_exception", $yaml) && $yaml["json_exception"] == true) $json->enableJsonException();
            return $json;
        }));
    }
}
