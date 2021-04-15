<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

use \Solital\Core\Course\Course;

Course::get('/user/{name}', 'UserController@show')->where(['name' => '[\w]+']);
$debugInfo = Course::startDebug();
echo sprintf('<pre>%s</pre>', var_export($debugInfo, true));
exit;