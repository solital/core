<?php

class DummyModelArg
{
    private $args1;
    private $args2;
    private $args3;

    public function __construct($args)
    {
        $this->args1 = $args[0];
        $this->args2 = $args[1];
        $this->args3 = $args[2];
    }

    public function execute()
    {
        $multiArgs = [$this->args1, $this->args2, $this->args3];
        #$res = implode('|', $multiArgs);
        
        return $multiArgs;
    }
}
