<?php

use Solital\Core\Http\Input\InputJson;
use Symfony\Component\VarDumper\VarDumper;
use Symfony\Component\VarExporter\VarExporter;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\AbstractDumper;

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
 * @return json
 */
function encodeJSON($value, int $constants = JSON_UNESCAPED_UNICODE)
{
    $json = (new InputJson($constants))->encode($value);
    return $json;
}

/**
 * @param mixed $value
 * @param bool $toArray
 * 
 * @return object|array
 */
function decodeJSON($value, bool $toArray = false)
{
    $json = (new InputJson())->decode($value, $toArray);
    return $json;
}
