<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Solital\Core\Course\Course;

$_SERVER["REQUEST_METHOD"] = 'get';
$_SERVER["REQUEST_URI"] = '/';

Course::get('/user/{name}', 'UserController@show')->where(['name' => '[\w]+']);
$debugInfo = Course::startDebug();
echo sprintf('<pre>%s</pre>', var_export($debugInfo, true));
exit;