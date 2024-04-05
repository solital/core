<?php

use Solital\Core\Resource\JSON;
use Solital\Core\Resource\Message;

/**
 * Show result pre-formatted
 * 
 * @param mixed $value
 * 
 * @return void
 */
function pre($value): void
{
    echo '<pre>';
    var_dump($value);
    echo '</pre>';
}

/**
 * Encode any value to JSON
 * 
 * @param mixed $value
 * @param int $constants
 * 
 * @return string
 */
function encodeJSON($value, int $constants = JSON_UNESCAPED_UNICODE): string
{
    return (new JSON($constants))->encode($value);
}

/**
 * Decode a JSON
 * 
 * @param mixed $value
 * @param bool $toArray
 * 
 * @return object|array
 */
function decodeJSON($value, bool $toArray = false): mixed
{
    return (new JSON())->decode($value, $toArray);
}

/**
 * Output data in Browser Console
 * 
 * @param mixed ...$messages
 * 
 * @return void
 */
function console_log(...$messages): void
{
    $msgs = '';
    foreach ($messages as $msg) {
        $msgs .= json_encode($msg);
    }

    echo '<script>';
    echo 'console.log(' . json_encode($msgs) . ')';
    echo '</script>';
}

/**
 * Create a message with `Message` class
 * 
 * @param string $key
 * @param string $msg
 */
function message(string $key, string $msg = "")
{
    $message = new Message();

    if ($msg == "" || empty($msg)) {
        return $message->get($key);
    } else {
        $message->new($key, $msg);
    }
}
