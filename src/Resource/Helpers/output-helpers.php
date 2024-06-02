<?php

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
