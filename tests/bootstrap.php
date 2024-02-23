<?php

use Solital\Core\Kernel\Application;

define('SITE_ROOT', dirname(__DIR__, 2));

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once 'mailConfig.php';

Application::autoload(dirname(__DIR__) . '/src/Resource/Helpers/');