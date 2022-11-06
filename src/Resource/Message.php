<?php

namespace Solital\Core\Resource;

use Solital\Core\Resource\Session;

final class Message
{
    /**
     * @param string $index
     * @param string $msg
     * 
     * @return void
     */
    public function new(string $index, string $msg): void
    {
        Session::set($index, $msg);
    }

    /**
     * @param string $index
     * 
     * @return null|string
     */
    public function get(string $index): ?string
    {
        if (isset($_SESSION[$index])) {
            try {
                return (string)$_SESSION[$index];
            } finally {
                Session::delete($index);
            }
        }

        return null;
    }
}
