<?php

namespace Solital\Core\Http\Controller;

use Solital\Core\Resource\Message;
use Solital\Core\Container\Container;
use Solital\Core\Logger\Logger;

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
     * @param string $channel
     * 
     * @return void
     */
    public function logger(string $channel): void
    {
        $this->logger = new Logger($channel);
    }
}
