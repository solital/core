<?php

namespace Solital\Core\Http\Controller;

use Solital\Core\Resource\Message;
use Solital\Core\Container\Container;
use Solital\Core\Logger\Logger;
use Solital\Core\Security\Hash;

trait BaseControllerTrait
{
    /**
     * @var Message
     */
    protected Message $message;

    /**
     * @var Container
     */
    protected Container $container;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->message = new Message();
        $this->container = new Container();
    }

    /**
     * Generate link to recovery password
     * 
     * @param string $email
     * @param string $time
     * 
     * @return string
     */
    public function generateRecoveryLink(string $email, string $uri, string $time): string
    {
        $hash = Hash::encrypt($email, $time);
        $link = $uri . $hash;

        return $link;
    }
}
