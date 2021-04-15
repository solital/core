<?php

namespace Solital\Core\Console\Command;

use Solital\Core\Course\Course;
use Solital\Core\Console\Style\Table;

class SystemCommandsCourse extends Course
{
    /**
     * @var mixed
     */
    private static $routes;

    public function __construct()
    {
        $this->table = new Table();
        #$this->table = new Table('', true, true);
    }

    /**
     * @param bool $send_console
     * 
     * @return void
     */
    public static function start(bool $send_console = false): void
    {
        $dir = SITE_ROOT_VINCI . DIRECTORY_SEPARATOR . 'routers' . DIRECTORY_SEPARATOR . '*.php';

        foreach (glob($dir) as $routers) {
            require_once $routers;
        }

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
    public function getRoutes(): void
    {
        $this->table->setTableColor('white');
        $this->table->setHeaderColor('white');
        $this->table->addField('URI', 'url', false, 'white');
        $this->table->addField('NAME', 'name', false, 'white');
        $this->table->addField('METHOD',  'method', false, 'white');
        $this->table->addField('CONTROLLER',  'controller', false, 'white');
        $this->table->injectData(self::$routes);
        $this->table->display();

        exit;
    }
}
