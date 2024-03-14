<?php

use Solital\Core\Kernel\Application;

define('SITE_ROOT', dirname(__DIR__, 2));

require_once dirname(__DIR__) . '/vendor/autoload.php';

$_SERVER["REQUEST_METHOD"] = 'get';
$_SERVER["REQUEST_URI"] = '/';

require_once 'mailConfig.php';
require_once 'TestRouter.php';

Application::autoload(dirname(__DIR__) . '/src/Resource/Helpers/');