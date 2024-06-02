<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Solital\Core\Kernel\{Application, DebugCore, Dotenv};
use Solital\Core\Security\Hash;

define('SITE_ROOT', dirname(__DIR__, 2));

$autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';

if (Application::fileExistsWithoutCache($autoload)) {
    echo 'Could not find "autoload.php". Did you run "composer update"?' . PHP_EOL;
    exit(1);
}

$_SERVER["REQUEST_METHOD"] = 'get';
$_SERVER["REQUEST_URI"] = '/';

require_once 'mailConfig.php';
require_once 'TestRouter.php';

$secret = Hash::randomString();

$_ENV['APP_HASH'] = $secret;
putenv("APP_HASH=$secret");

DebugCore::enableCoreDebug();
DebugCore::setDatabaseConnection('mysql', 'localhost', 'db_debug', 'user', 'pass');

Application::autoload(dirname(__DIR__) . '/src/Resource/Helpers/');
Dotenv::env(dirname(__DIR__));
