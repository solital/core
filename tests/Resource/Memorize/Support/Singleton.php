<?php

namespace Solital\Test\Resource\Memorize\Support;

#[\AllowDynamicProperties]
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
