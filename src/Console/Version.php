<?php

namespace Solital\Core\Console;

use Katrina\Katrina;

class Version
{
    const SOLITAL_VERSION = "2.4.0";

    /**
     * @return string
     */
    public static function katrinaVersion(): string
    {
        return Katrina::KATRINA_VERSION;
    }

    /**
     * @return string
     */
    public static function phpVersion(): string
    {
        return PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION . "." . PHP_RELEASE_VERSION;
    }
}
