<?php

namespace Solital\Test\Resource\Memorize\Support;

class Singleton
{
    private function __construct()
    {
        // private
    }

    public static function getInstance()
    {
        return memorize(function () {
            return new Singleton();
        });
    }
}
