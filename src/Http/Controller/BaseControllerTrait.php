<?php

namespace Solital\Core\Http\Controller;

use Solital\Core\Resource\Message;
use Solital\Core\Container\Container;

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
     * Construct
     */
    public function __construct()
    {
        $this->message = new Message();
        $this->container = new Container();
    }
}
