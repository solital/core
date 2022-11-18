<?php

use Solital\Core\Resource\JSON;
use Solital\Core\Resource\Message;
use Symfony\Component\VarExporter\VarExporter;
use Symfony\Component\VarDumper\{Cloner\VarCloner, Dumper\CliDumper, Dumper\AbstractDumper};

/**
 * Show result pre-formatted
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
 * @param mixed $var
 * 
 * @return mixed
 */
function cloner($var)
{
    $cloner = new VarCloner();
    $data = $cloner->cloneVar($var);
    dump($data);
}

/**
 * @param mixed $var
 * @param bool $length
 * 
 * @return mixed
 */
function dumper($var, bool $length = false)
{
    if ($length == true) {
        $varCloner = new VarCloner();
        $dumper = new CliDumper(null, null, AbstractDumper::DUMP_STRING_LENGTH | AbstractDumper::DUMP_STRING_LENGTH);
        $output = $dumper->dump($varCloner->cloneVar($var), true);
        dump($output);

        return;
    }

    $cloner = new VarCloner();
    $dumper = new CliDumper();
    $output = fopen('php://memory', 'r+b');

    $dumper->dump($cloner->cloneVar($var), $output);
    $output = stream_get_contents($output, -1, 0);
    dump($output);
}

/**
 * @param mixed $value
 * 
 * @return void
 */
function export($value): void
{
    $res = VarExporter::export($value);
    echo $res;
}

/**
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
