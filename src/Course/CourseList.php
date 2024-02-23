<?php

namespace Solital\Core\Course;

use Solital\Core\Console\Table;
use Solital\Core\Course\Course;
use Solital\Core\Kernel\Application;

class CourseList extends Course
{
    /**
     * @var mixed
     */
    private static $routes;

    final public function __construct()
    {
        
    }

    /**
     * @param bool $send_console
     * 
     * @return void
     */
    public static function start(bool $send_console = false): void
    {
        $dir = constant('SITE_ROOT') . DIRECTORY_SEPARATOR . 'routers';

        Application::autoload($dir);

        parent::start($send_console);
    }

    /**
     * @param mixed $routes
     * 
     * @return static
     */
    public static function setRoutes($routes)
    {
        self::$routes = $routes;
        return new static();
    }

    /**
     * @return void
     */
    public function getRoutes(): never
    {
        $headers = ["url", "name", "method", "controller"];

        $table = new Table();
        $table->setHeaderStyle(Table::COLOR_LIGHT_GREEN);
        $table->dynamicRows($headers, self::$routes);
        echo $table;

        exit;
    }
}
