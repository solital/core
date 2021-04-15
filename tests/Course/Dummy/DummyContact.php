<?php

class DummyContact
{
    public function __construct($args)
    {
        echo "\n".$args."\n";
        return $args;    
    }

    public function call()
    {
        return 'Contact';
    }
}
